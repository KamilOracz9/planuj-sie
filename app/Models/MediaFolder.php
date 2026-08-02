<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['id', 'type', 'name', 'parent_id'])]
class MediaFolder extends BaseModel
{
    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    /**
     * Delete the folder without losing anything inside it: subfolders and any
     * media directly in this folder are re-parented one level up first.
     */
    public function deleteAndReparentContents(): void
    {
        $this->children()->update(['parent_id' => $this->parent_id]);

        Gallery::instance()
            ->getMedia($this->type)
            ->filter(fn($media) => (int) $media->getCustomProperty('folder_id') === $this->id)
            ->each(function ($media) {
                $media->setCustomProperty('folder_id', $this->parent_id);
                $media->save();
            });

        $this->delete();
    }

    /**
     * True if $candidateId is this folder itself or one of its descendants —
     * i.e. moving this folder under $candidateId would create a cycle.
     */
    public function isSelfOrDescendant(int $candidateId): bool
    {
        $current = static::query()->where('type', $this->type)->find($candidateId);

        while ($current) {
            if ($current->id === $this->id) {
                return true;
            }

            $current = $current->parent_id ? static::query()->where('type', $this->type)->find($current->parent_id) : null;
        }

        return false;
    }
}
