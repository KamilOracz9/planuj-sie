<?php

namespace App\Http\Controllers\PanelControllers\Media;

use App\Http\Controllers\Controller;
use App\Http\Resources\MediaResource;
use App\Models\Gallery;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

abstract class BaseMediaController extends Controller
{
    /** @var class-string */
    protected string $modelClass;

    /** @var array<string, bool> collection name => is single file */
    protected array $collections = [];

    public function index(int $id)
    {
        $model = $this->modelClass::findOrFail($id);

        $data = [];

        foreach ($this->collections as $collection => $single) {
            $media = $model->getMedia($collection);

            $data[$collection] = $single
                ? ($media->first() ? new MediaResource($media->first()) : null)
                : MediaResource::collection($media->sortBy('order_column')->values());
        }

        return response()->json($data);
    }

    public function store(Request $request, int $id)
    {
        $collection = $request->input('collection');
        $isDocument = $collection === 'documents';

        $request->validate([
            'collection' => ['required', 'string', Rule::in(array_keys($this->collections))],
            'files' => ['required', 'array', 'min:1'],
            'files.*' => $isDocument
                ? ['required', 'file', 'mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,txt,csv', 'max:20480']
                : ['required', 'image', 'mimes:jpeg,png,jpg,webp', 'max:10240'],
        ]);

        $model = $this->modelClass::findOrFail($id);
        $collection = $request->string('collection')->value();

        if ($this->collections[$collection] && count($request->file('files')) > 1) {
            return response()->json(['message' => 'This collection only accepts a single file.'], 422);
        }

        $media = collect($request->file('files'))
            ->map(fn($file) => $model->addMedia($file)->toMediaCollection($collection));

        return response()->json(MediaResource::collection($media), 201);
    }

    public function attach(Request $request, int $id)
    {
        $request->validate([
            'collection' => ['required', 'string', Rule::in(array_keys($this->collections))],
            'media_id' => ['required', 'integer'],
        ]);

        $model = $this->modelClass::findOrFail($id);
        $collection = $request->string('collection')->value();
        $sourceCollection = $collection === 'documents' ? 'documents' : 'images';

        $sourceMedia = Media::query()
            ->where('model_type', Gallery::class)
            ->where('collection_name', $sourceCollection)
            ->findOrFail($request->integer('media_id'));

        // ->toMediaCollection() (called internally by copy()) already enforces
        // singleFile() collections, replacing any existing media automatically.
        $newMedia = $sourceMedia->copy($model, $collection);

        return response()->json(new MediaResource($newMedia), 201);
    }

    public function destroy(int $id, int $mediaId)
    {
        $model = $this->modelClass::findOrFail($id);

        $media = $model->media()->findOrFail($mediaId);

        $media->delete();

        return response()->json(['id' => $mediaId]);
    }

    public function reorder(Request $request, int $id)
    {
        $request->validate([
            'collection' => ['required', 'string', Rule::in(array_keys($this->collections))],
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer'],
        ]);

        $model = $this->modelClass::findOrFail($id);
        $collection = $request->string('collection')->value();

        $ids = Media::query()
            ->where('model_type', $model->getMorphClass())
            ->where('model_id', $model->id)
            ->where('collection_name', $collection)
            ->whereIn('id', $request->input('ids'))
            ->pluck('id')
            ->all();

        Media::setNewOrder($ids);

        return response()->json(['ids' => $ids]);
    }
}
