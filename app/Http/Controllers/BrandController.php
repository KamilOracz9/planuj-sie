<?php

namespace App\Http\Controllers;

use App\Enums\CacheKeys;
use App\Http\Requests\BrandRequest;
use App\Http\Resources\BrandResource;
use App\Models\Brand;

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

    public function update(BrandRequest $request, int $id)
    {
        $brand = Brand::findOrFail($id);

        $brand->fill($request->query());

        $brand->save();

        return response()->json(new BrandResource($brand));
    }

    public function create(BrandRequest $request)
    {
        $brand = new Brand($request->query());

        $brand->save();

        return response()->json(new BrandResource($brand), 201);
    }

    public function destroy(int $id)
    {
        $brand = Brand::findOrFail($id);

        $brand->delete();

        return response()->json(new BrandResource($brand));
    }
}
