<?php

namespace App\Http\Controllers;

use App\Enums\CacheKeys;
use App\Http\Resources\BrandResource;
use App\Models\Brand;
use Illuminate\Http\Request;

class BrandController extends Controller
{
    public function index(string $locale)
    {
        app()->setLocale($locale);

        $brands = cache()->remember(
            CacheKeys::BRANDS_LIST->value,
            config('app.cache_lifetime'),
            fn() => Brand::select(...Brand::LIST_FIELDS)
                ->get()
                ->toResourceCollection(BrandResource::class)
                ->toArray(request())
        );

        return response()->json($brands);
    }

    public function show(string $locale, int $id)
    {
        app()->setLocale($locale);

        $brand = new BrandResource(Brand::findOrFail($id));

        return response()->json($brand);
    }

    public function update(Request $request, int $id)
    {
        $brand = Brand::findOrFail($id);

        $brand->fill($request->query());

        $brand->save();

        return response()->json($brand);
    }

    public function create(Request $request)
    {
        $brand = new Brand($request->query());

        $brand->save();

        return response()->json($brand, 201);
    }

    public function destroy(int $id)
    {
        $brand = Brand::findOrFail($id);

        $brand->delete();

        return response()->json($brand);
    }
}
