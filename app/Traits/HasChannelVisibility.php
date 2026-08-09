<?php

namespace App\Traits;

use App\Models\ChannelVisibility;
use Illuminate\Support\Facades\DB;

trait HasChannelVisibility
{
    protected static function bootChannelVisibility()
    {
        static::saved(function ($model) {
            $channels = request()->input('channels');

            if (!is_array($channels)) {
                return;
            }

            DB::transaction(function () use ($model, $channels) {
                $submittedChannelIds = array_map(fn($channel) => (int) $channel['channel_id'], $channels);

                ChannelVisibility::query()
                    ->where('model_type', get_class($model))
                    ->where('model_id', $model->id)
                    ->whereNotIn('channel_id', $submittedChannelIds)
                    ->delete();

                foreach ($channels as $channel) {
                    ChannelVisibility::query()->updateOrCreate(
                        [
                            'model_id' => $model->id,
                            'model_type' => get_class($model),
                            'channel_id' => $channel['channel_id'],
                        ],
                        [
                            'is_enabled' => filter_var($channel['is_enabled'], FILTER_VALIDATE_BOOLEAN),
                        ]
                    );
                }
            });
        });

        static::deleted(function ($model) {
            ChannelVisibility::query()
                ->where('model_type', get_class($model))
                ->where('model_id', $model->id)
                ->delete();
        });
    }

    public function isEnabledForChannel(int $channelId): bool
    {
        $row = ChannelVisibility::query()
            ->where('model_type', get_class($this))
            ->where('model_id', $this->id)
            ->where('channel_id', $channelId)
            ->first();

        return $row ? (bool) $row->is_enabled : true;
    }

    /**
     * Outer array = dimensions ANDed together; inner array = candidates ORed
     * within a dimension. An empty inner group means "no ancestor in that
     * dimension" and is skipped, not treated as blocking. Override per model.
     */
    public function ancestorGroupsForVisibility(): array
    {
        return [];
    }

    public function isVisibleInChannel(int $channelId): bool
    {
        if (!$this->isEnabledForChannel($channelId)) {
            return false;
        }

        foreach ($this->ancestorGroupsForVisibility() as $group) {
            $group = array_filter($group);

            if (empty($group)) {
                continue;
            }

            $groupVisible = false;

            foreach ($group as $ancestor) {
                if ($ancestor->isVisibleInChannel($channelId)) {
                    $groupVisible = true;
                    break;
                }
            }

            if (!$groupVisible) {
                return false;
            }
        }

        return true;
    }
}
