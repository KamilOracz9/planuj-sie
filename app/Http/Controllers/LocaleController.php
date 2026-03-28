<?php

namespace App\Http\Controllers;

use App\Enums\CacheKeys;
use App\Http\Requests\LocaleRequest;
use App\Http\Resources\LocaleResource;
use App\Models\Locale;
use App\Models\Translations\LocaleTranslation;

class LocaleController extends Controller
{
    public function index(string $locale)
    {
        $data = cache()->remember(
            CacheKeys::LOCALES_LIST->value . "_$locale",
            config('app.cache_lifetime'),
            fn() => Locale::queryBuilder()
                ->withTranslation(LocaleTranslation::class, $locale, 'id', 'locale_id', Locale::class)
                ->listSelect()
                ->get()
                ->map(fn($item) => (array) $item)
                ->toArray()
        );
    
        return response()->json($data);
    }

    public function show(string $locale, int $id)
    {
        $locale = new LocaleResource(
            Locale::queryBuilder()
                ->withTranslation(LocaleTranslation::class, $locale, 'id', 'locale_id', Locale::class)
                ->where(Locale::columnName('id'), $id)
                ->listSelect()
                ->first()
        );

        return response()->json($locale);
    }

    public function update(LocaleRequest $request, int $id)
    {
        $locale = Locale::findOrFail($id);

        $locale->update($request->query());

        return response()->json(new LocaleResource($locale));
    }

    public function create(LocaleRequest $request)
    {
        $locale = new Locale($request->query());

        $locale->save();

        return response()->json(new LocaleResource($locale), 201);
    }

    public function destroy(int $id)
    {
        $locale = Locale::findOrFail($id);

        $locale->delete();

        return response()->json(new LocaleResource($locale));
    }
}
