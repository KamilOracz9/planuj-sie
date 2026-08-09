<?php

namespace App\Http\Controllers\PanelControllers\Media;

use App\Http\Controllers\Controller;
use App\Http\Resources\MediaResource;
use App\Models\Channel;
use App\Models\Gallery;
use App\Models\MediaCollection;
use App\Models\MediaCollectionAssignment;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

// Replaces BaseMediaController + its 9 per-model subclasses: collections are
// now data-driven (MediaCollection rows, resolved by `code`) instead of a
// hardcoded $collections array per subclass, and every upload is scoped to
// an explicit channel_id (no fallback - see media rework plan decision #3).
class MediaController extends Controller
{
    public function index(string $modelType, int $id)
    {
        $modelClass = $this->resolveModelClass($modelType);
        $model = $modelClass::findOrFail($id);

        // Grouped by whatever collections the model actually has media in -
        // not gated by the current assignment config (which only controls
        // what's *offered for upload*, see ensureAssigned()), so media
        // already uploaded stays visible even if an assignment is later removed.
        $data = [];

        foreach ($model->media->groupBy('collection_name') as $code => $media) {
            $data[$code] = MediaResource::collection($media->sortBy('order_column')->values());
        }

        return response()->json($data);
    }

    public function store(Request $request, string $modelType, int $id)
    {
        $modelClass = $this->resolveModelClass($modelType);
        $model = $modelClass::findOrFail($id);

        $request->validate([
            'collection' => ['required', 'string', Rule::exists(MediaCollection::tableName(), 'code')],
            'channel_id' => ['required', 'integer', Rule::exists(Channel::tableName(), 'id')],
        ]);

        $mediaCollection = MediaCollection::where('code', $request->string('collection')->value())->firstOrFail();
        $channelId = $request->integer('channel_id');

        $this->ensureAssigned($modelType, $mediaCollection->id, $channelId);

        $isDocument = $mediaCollection->kind === 'document';

        $request->validate([
            'files' => ['required', 'array', 'min:1'],
            'files.*' => $isDocument
                ? ['required', 'file', 'mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,txt,csv', 'max:20480']
                : ['required', 'image', 'mimes:jpeg,png,jpg,webp', 'max:10240'],
        ]);

        if ($mediaCollection->type === 'single' && count($request->file('files')) > 1) {
            return response()->json(['message' => 'This collection only accepts a single file.'], 422);
        }

        if ($mediaCollection->type === 'single') {
            // Enforced here, not via Spatie's singleFile(): that mechanism is
            // scoped to (model, collection), not (model, collection, channel),
            // so it would wipe channel A's file when uploading for channel B.
            Media::query()
                ->where('model_type', $modelClass)
                ->where('model_id', $id)
                ->where('collection_name', $mediaCollection->code)
                ->where('channel_id', $channelId)
                ->get()
                ->each->delete();
        }

        $media = collect($request->file('files'))
            ->map(function ($file) use ($model, $mediaCollection, $channelId) {
                // channel_id must be set via withProperties() BEFORE
                // toMediaCollection(), not with a follow-up update() - Spatie
                // generates conversions synchronously inside toMediaCollection()
                // (registerMediaConversions() reads $media->channel_id), so a
                // later update() would be too late and every conversion would
                // silently match nothing.
                return $model->addMedia($file)
                    ->withProperties(['channel_id' => $channelId])
                    ->toMediaCollection($mediaCollection->code);
            });

        return response()->json(MediaResource::collection($media), 201);
    }

    public function attach(Request $request, string $modelType, int $id)
    {
        $modelClass = $this->resolveModelClass($modelType);
        $model = $modelClass::findOrFail($id);

        $request->validate([
            'collection' => ['required', 'string', Rule::exists(MediaCollection::tableName(), 'code')],
            'channel_id' => ['required', 'integer', Rule::exists(Channel::tableName(), 'id')],
            'media_id' => ['required', 'integer'],
        ]);

        $mediaCollection = MediaCollection::where('code', $request->string('collection')->value())->firstOrFail();
        $channelId = $request->integer('channel_id');

        $this->ensureAssigned($modelType, $mediaCollection->id, $channelId);

        $sourceCollection = $mediaCollection->kind === 'document' ? 'documents' : 'images';

        $sourceMedia = Media::query()
            ->where('model_type', Gallery::class)
            ->where('collection_name', $sourceCollection)
            ->findOrFail($request->integer('media_id'));

        if ($mediaCollection->type === 'single') {
            Media::query()
                ->where('model_type', $modelClass)
                ->where('model_id', $id)
                ->where('collection_name', $mediaCollection->code)
                ->where('channel_id', $channelId)
                ->get()
                ->each->delete();
        }

        // copy() calls toMediaCollection() internally, which generates
        // conversions synchronously - channel_id must already be set by then
        // (registerMediaConversions() reads it), so it's passed through the
        // fileAdderCallback rather than fixed up with a follow-up update().
        $newMedia = $sourceMedia->copy(
            $model,
            $mediaCollection->code,
            fileAdderCallback: fn($fileAdder) => $fileAdder->withProperties(['channel_id' => $channelId])
        );

        return response()->json(new MediaResource($newMedia), 201);
    }

    public function destroy(string $modelType, int $id, int $mediaId)
    {
        $modelClass = $this->resolveModelClass($modelType);
        $model = $modelClass::findOrFail($id);

        $media = $model->media()->findOrFail($mediaId);

        $media->delete();

        return response()->json(['id' => $mediaId]);
    }

    public function reorder(Request $request, string $modelType, int $id)
    {
        $modelClass = $this->resolveModelClass($modelType);
        $model = $modelClass::findOrFail($id);

        $request->validate([
            'collection' => ['required', 'string', Rule::exists(MediaCollection::tableName(), 'code')],
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer'],
        ]);

        $collection = $request->string('collection')->value();

        $ids = Media::query()
            ->where('model_type', $modelClass)
            ->where('model_id', $model->id)
            ->where('collection_name', $collection)
            ->whereIn('id', $request->input('ids'))
            ->pluck('id')
            ->all();

        Media::setNewOrder($ids);

        return response()->json(['ids' => $ids]);
    }

    private function resolveModelClass(string $modelType): string
    {
        $modelClass = config('media.model_types')[$modelType] ?? null;

        abort_if(!$modelClass, 404, 'Unknown model type.');

        return $modelClass;
    }

    // Gated by (channel, model TYPE) now, not by model instance - see the
    // "Przypisania" tab on MediaCollection's own edit page.
    private function ensureAssigned(string $modelType, int $mediaCollectionId, int $channelId): void
    {
        $assigned = MediaCollectionAssignment::query()
            ->where('media_collection_id', $mediaCollectionId)
            ->where('channel_id', $channelId)
            ->where('model_type', $modelType)
            ->exists();

        abort_unless($assigned, 422, 'This media collection is not assigned to this model type in this channel.');
    }
}
