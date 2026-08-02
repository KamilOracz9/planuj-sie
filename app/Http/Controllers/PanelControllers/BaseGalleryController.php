<?php

namespace App\Http\Controllers\PanelControllers;

use App\Http\Controllers\Controller;
use App\Http\Resources\MediaResource;
use App\Models\Gallery;
use App\Models\MediaFolder;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

abstract class BaseGalleryController extends Controller
{
    /** The Spatie media collection this library reads/writes ('images' or 'documents'). */
    protected string $collection;

    /** Validation rules applied to each uploaded file. */
    protected array $fileRules;

    public function index(Request $request)
    {
        // "All" (no folder_id in the request at all) shows every file regardless of
        // folder; a specific folder_id filters down to just that folder's contents.
        $media = $request->has('folder_id')
            ? Gallery::instance()->getMedia(
                $this->collection,
                fn($media) => (int) $media->getCustomProperty('folder_id') === $request->integer('folder_id')
            )
            : Gallery::instance()->getMedia($this->collection);

        return response()->json(MediaResource::collection($media->sortByDesc('created_at')->values()));
    }

    public function store(Request $request)
    {
        $request->validate([
            'files' => ['required', 'array', 'min:1'],
            'files.*' => $this->fileRules,
            'folder_id' => ['nullable', 'integer', Rule::exists(MediaFolder::class, 'id')->where('type', $this->collection)],
        ]);

        $gallery = Gallery::instance();
        $folderId = $request->integer('folder_id') ?: null;

        $media = collect($request->file('files'))
            ->map(fn($file) => $gallery->addMedia($file)
                ->withCustomProperties(['folder_id' => $folderId])
                ->toMediaCollection($this->collection));

        return response()->json(MediaResource::collection($media), 201);
    }

    public function move(Request $request, int $mediaId)
    {
        $request->validate([
            'folder_id' => ['nullable', 'integer', Rule::exists(MediaFolder::class, 'id')->where('type', $this->collection)],
        ]);

        $media = Media::query()
            ->where('model_type', Gallery::class)
            ->where('collection_name', $this->collection)
            ->findOrFail($mediaId);

        $media->setCustomProperty('folder_id', $request->integer('folder_id') ?: null);
        $media->save();

        return response()->json(new MediaResource($media));
    }

    public function destroy(int $mediaId)
    {
        $media = Media::query()
            ->where('model_type', Gallery::class)
            ->where('collection_name', $this->collection)
            ->findOrFail($mediaId);

        $media->delete();

        return response()->json(['id' => $mediaId]);
    }
}
