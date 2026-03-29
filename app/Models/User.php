<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Route;
use Tymon\JWTAuth\Contracts\JWTSubject;

#[Fillable(['name', 'email', 'password'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable implements JWTSubject
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

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

    public static function routes()
    {
        return Route::group(['prefix' => 'users'], function () {
            Route::get('/', [\App\Http\Controllers\PanelControllers\UserController::class, 'index']);
            Route::get('/{id}', [\App\Http\Controllers\PanelControllers\UserController::class, 'show']);
            Route::post('/create', [\App\Http\Controllers\PanelControllers\UserController::class, 'create']);
            Route::delete('/{id}', [\App\Http\Controllers\PanelControllers\UserController::class, 'destroy']);
        });
    }
}
