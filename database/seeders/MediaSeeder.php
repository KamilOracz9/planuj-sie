<?php

namespace Database\Seeders;

use App\Models\AttributeOption;
use App\Models\Brand;
use App\Models\Product;
use App\Models\Variant;
use Illuminate\Database\Seeder;

// Deliberately does NOT `use WithoutModelEvents` (unlike every other seeder
// here): this one drives the real Spatie pipeline through actual model
// instances (Product::find()->addMedia()->toMediaCollection()), the same
// way the panel itself does it - suppressing model events isn't needed
// (nothing here does a raw translation-table insert that could race with
// one) and would risk interfering with Spatie's own internals for no
// benefit.
//
// Runs last: needs Products/Variants/Brands/AttributeOptions (their own
// seeders) and the media_collection_assignments/media_collection_conversions
// (their own seeders) to already exist - registerMediaConversions() looks
// conversions up live per (collection, channel) at upload time.
class MediaSeeder extends Seeder
{
    public function run(): void
    {
        $brand = Brand::findOrFail(1);
        $brand->addMedia($this->placeholder('#2D3748'))
            ->withProperties(['channel_id' => 1])
            ->toMediaCollection('logo');

        $product1 = Product::findOrFail(1);
        // Default channel: main_image + packshot.
        $product1->addMedia($this->placeholder('#E53E3E'))
            ->withProperties(['channel_id' => 1])
            ->toMediaCollection('main_image');
        $product1->addMedia($this->placeholder('#E53E3E'))
            ->withProperties(['channel_id' => 1])
            ->toMediaCollection('packshot');
        // Marketplace channel: a DIFFERENT image via listing_image - same
        // product, genuinely different media depending on the channel.
        $product1->addMedia($this->placeholder('#3182CE'))
            ->withProperties(['channel_id' => 3])
            ->toMediaCollection('listing_image');
        // Also main_image on Marketplace - main_image is assigned on all 3
        // channels (unlike packshot/listing_image), so this is what proves
        // the "thumb" conversion (see MediaCollectionConversionSeeder)
        // actually differs in size between channels for the same collection.
        $product1->addMedia($this->placeholder('#3182CE'))
            ->withProperties(['channel_id' => 3])
            ->toMediaCollection('main_image');

        $product2 = Product::findOrFail(2);
        $product2->addMedia($this->placeholder('#38A169'))
            ->withProperties(['channel_id' => 1])
            ->toMediaCollection('main_image');

        $variant1 = Variant::findOrFail(1);
        $variant1->addMedia($this->placeholder('#DD6B20'))
            ->withProperties(['channel_id' => 1])
            ->toMediaCollection('packshot');

        // "Czerwony" (Red) color option - the icon is fittingly red too.
        $colorOption = AttributeOption::findOrFail(1);
        $colorOption->addMedia($this->placeholder('#E53E3E'))
            ->withProperties(['channel_id' => 1])
            ->toMediaCollection('icon');
    }

    private function placeholder(string $hexColor): string
    {
        [$r, $g, $b] = sscanf($hexColor, '#%02x%02x%02x');

        $image = imagecreatetruecolor(400, 400);
        imagefill($image, 0, 0, imagecolorallocate($image, $r, $g, $b));

        $path = sys_get_temp_dir() . '/seed_media_' . bin2hex(random_bytes(8)) . '.jpg';
        imagejpeg($image, $path);

        return $path;
    }
}
