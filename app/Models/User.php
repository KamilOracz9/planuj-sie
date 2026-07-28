<?php

namespace App\Models;

use App\Enums\CacheKeys;
use App\QueryBuilders\UserQueryBuilder;
use App\Traits\HasCache;
use Illuminate\Auth\Authenticatable;
use Illuminate\Auth\MustVerifyEmail;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\Access\Authorizable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Route;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;

#[Fillable(['name', 'email', 'password'])]
#[Hidden(['password', 'remember_token'])]
class User extends BaseModel implements
    AuthenticatableContract,
    AuthorizableContract,
    CanResetPasswordContract,
    JWTSubject
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, HasCache, Authenticatable, Authorizable, CanResetPassword, MustVerifyEmail;

    protected static function boot()
    {
        parent::boot();

        static::bootCache();
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }

    private static function clearCache($model = null)
    {
        static::clearLocaleCache([CacheKeys::USERS_LIST->value]);

        if ($model) {
            static::clearShowCache([CacheKeys::USERS_LIST->value], $model->id);
        }
    }

    public static function routes()
    {
        return [
            Route::group(['prefix' => 'users'], function () {
                Route::put('/{id}', [\App\Http\Controllers\PanelControllers\UserController::class, 'update']);
                Route::post('/', [\App\Http\Controllers\PanelControllers\UserController::class, 'create']);
                Route::delete('/{id}', [\App\Http\Controllers\PanelControllers\UserController::class, 'destroy']);
            }),
            Route::group(['prefix' => '{locale}'], function () {
                Route::group(['prefix' => 'users'], function () {
                    Route::get('/', [\App\Http\Controllers\PanelControllers\UserController::class, 'index']);
                    Route::get('/{id}', [\App\Http\Controllers\PanelControllers\UserController::class, 'show']);
                });
            })
        ];
    }

    public static function newQueryBuilder()
    {
        return new UserQueryBuilder();
    }
}
