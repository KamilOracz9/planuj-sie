<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * One-off migration helper for the media-library disk switching from
 * 'public' to a private disk (see the api-security skill): copies every
 * media file and its conversions to the new disk, then repoints the
 * `media` rows at it. Does NOT delete the old files - verify first, then
 * clean up storage/app/public manually.
 */
class MoveMediaToPrivateDisk extends Command
{
    protected $signature = 'media:move-to-private-disk {--from=public} {--to=media}';

    protected $description = 'Copy every media file (and its conversions) from one disk to another, then repoint the media rows at the new disk';

    public function handle(): int
    {
        $from = $this->option('from');
        $to = $this->option('to');

        $fromRoot = rtrim(config("filesystems.disks.{$from}.root"), '/');
        $toRoot = rtrim(config("filesystems.disks.{$to}.root"), '/');

        $media = Media::where('disk', $from)->orWhere('conversions_disk', $from)->get();

        if ($media->isEmpty()) {
            $this->info("No media rows reference disk [{$from}].");

            return self::SUCCESS;
        }

        foreach ($media as $item) {
            $sourceDir = "{$fromRoot}/{$item->id}";
            $targetDir = "{$toRoot}/{$item->id}";

            if (! is_dir($sourceDir)) {
                $this->warn("Media #{$item->id}: source directory not found at {$sourceDir}, skipping file copy.");
            } else {
                File::ensureDirectoryExists(dirname($targetDir));
                File::copyDirectory($sourceDir, $targetDir);
                $this->line("Copied media #{$item->id} -> {$targetDir}");
            }

            $item->forceFill([
                'disk' => $to,
                'conversions_disk' => $to,
            ])->save();
        }

        $this->info("Repointed {$media->count()} media row(s) from [{$from}] to [{$to}]. Old files under {$fromRoot} were left in place for verification - remove them manually once confirmed.");

        return self::SUCCESS;
    }
}
