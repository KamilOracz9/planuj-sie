<?php

namespace App\Http\Controllers\PanelControllers;

use App\Http\Controllers\Controller;
use App\Http\Resources\MediaResource;
use App\Models\Gallery;
use Illuminate\Http\Request;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class GalleryController extends Controller
{
    public function index()
    {
        $media = Gallery::instance()
            ->getMedia('images')
            ->sortByDesc('created_at')
            ->values();

        return response()->json(MediaResource::collection($media));
    }

    public function store(Request $request)
    {
        $request->validate([
            'files' => ['required', 'array', 'min:1'],
            'files.*' => ['required', 'image', 'mimes:jpeg,png,jpg,webp', 'max:10240'],
        ]);

        $gallery = Gallery::instance();

        $media = collect($request->file('files'))
            ->map(fn($file) => $gallery->addMedia($file)->toMediaCollection('images'));

        return response()->json(MediaResource::collection($media), 201);
    }

    public function destroy(int $mediaId)
    {
        $media = Media::query()
            ->where('model_type', Gallery::class)
            ->where('collection_name', 'images')
            ->findOrFail($mediaId);

        $media->delete();

        return response()->json(['id' => $mediaId]);
    }
}
