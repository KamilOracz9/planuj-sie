<?php

namespace App\Http\Controllers\PanelControllers;

use App\Http\Controllers\Controller;
use App\Models\MediaFolder;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class MediaFolderController extends Controller
{
    private const TYPES = ['images', 'documents'];

    public function index(string $type)
    {
        abort_unless(in_array($type, self::TYPES), 404);

        $folders = MediaFolder::query()
            ->where('type', $type)
            ->orderBy('name')
            ->get(['id', 'name', 'parent_id']);

        return response()->json($folders);
    }

    public function store(Request $request, string $type)
    {
        abort_unless(in_array($type, self::TYPES), 404);

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'parent_id' => ['nullable', 'integer', Rule::exists(MediaFolder::class, 'id')->where('type', $type)],
        ]);

        $folder = MediaFolder::create([
            'type' => $type,
            'name' => $request->input('name'),
            'parent_id' => $request->integer('parent_id') ?: null,
        ]);

        return response()->json($folder->only(['id', 'name', 'parent_id']), 201);
    }

    public function update(Request $request, string $type, int $id)
    {
        abort_unless(in_array($type, self::TYPES), 404);

        $folder = MediaFolder::query()->where('type', $type)->findOrFail($id);

        $request->validate([
            'parent_id' => ['nullable', 'integer', Rule::exists(MediaFolder::class, 'id')->where('type', $type)],
        ]);

        $parentId = $request->integer('parent_id') ?: null;

        if ($parentId !== null && $folder->isSelfOrDescendant($parentId)) {
            return response()->json(['message' => 'Cannot move a folder into itself or one of its own subfolders.'], 422);
        }

        $folder->update(['parent_id' => $parentId]);

        return response()->json($folder->only(['id', 'name', 'parent_id']));
    }

    public function destroy(string $type, int $id)
    {
        abort_unless(in_array($type, self::TYPES), 404);

        $folder = MediaFolder::query()->where('type', $type)->findOrFail($id);

        $folder->deleteAndReparentContents();

        return response()->json(['id' => $id]);
    }
}
