<?php

namespace App\Models;

use App\Enums\CacheKeys;
use App\QueryBuilders\CurrencyQueryBuilder;
use App\Traits\HasCache;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Support\Facades\Route;

#[Fillable(['id', 'code', 'name', 'symbol', 'decimal_places'])]
class Currency extends BaseModel
{
    use HasCache;

    // No HasTranslations/Sluggable: code/name/symbol are ISO reference data,
    // not translatable catalog content. BaseRequest::prepareForValidation()
    // checks isSluggable before touching the (nonexistent) sluggable property,
    // so this must be declared explicitly rather than relying on Sluggable.
    public bool $isSluggable = false;

    protected static function boot()
    {
        parent::boot();

        static::bootCache();
    }

    public static function routes()
    {
        return [
            Route::group(['prefix' => 'currencies'], function () {
                Route::put('/{id}', [\App\Http\Controllers\PanelControllers\CurrencyController::class, 'update']);
                Route::post('/', [\App\Http\Controllers\PanelControllers\CurrencyController::class, 'create']);
                Route::delete('/{id}', [\App\Http\Controllers\PanelControllers\CurrencyController::class, 'destroy']);
            }),
            Route::group(['prefix' => '{locale}'], function () {
                Route::group(['prefix' => 'currencies'], function () {
                    Route::get('/', [\App\Http\Controllers\PanelControllers\CurrencyController::class, 'index']);
                    Route::get('/select', [\App\Http\Controllers\PanelControllers\CurrencyController::class, 'select']);
                    Route::get('/{id}', [\App\Http\Controllers\PanelControllers\CurrencyController::class, 'show']);
                });
            })
        ];
    }

    private static function clearCache($model = null)
    {
        static::clearLocaleCache([CacheKeys::CURRENCIES_LIST->value, CacheKeys::CURRENCIES_SELECT->value]);

        if ($model) {
            static::clearShowCache([CacheKeys::CURRENCIES_LIST->value], $model->id);
        }
    }

    public static function newQueryBuilder()
    {
        return new CurrencyQueryBuilder();
    }

    // Money is stored (in `prices.amount`) as an integer in the currency's
    // smallest unit, e.g. 100.00 PLN (decimal_places=2) -> 10000 - these two
    // helpers are the single place that conversion happens, used by
    // App\Traits\HasPrices (write) and PriceController::selectByModel (read).
    public function toMinorUnits(int|float|string $majorAmount): int
    {
        return (int) round(((float) $majorAmount) * (10 ** $this->decimal_places));
    }

    public function toMajorUnits(int $minorAmount): float
    {
        return round($minorAmount / (10 ** $this->decimal_places), $this->decimal_places);
    }
}
