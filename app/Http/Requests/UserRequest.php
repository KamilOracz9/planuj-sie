<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Validation\Rule;

class UserRequest extends BaseRequest
{
    protected string $modelClass = User::class;

    public function rules(): array
    {
        $userId = $this->route('id');

        return [
            'name' => ['required', 'string', 'max:255', Rule::unique(User::tableName(), 'name')->ignore($userId, 'name')],
            'email' => ['required', 'email', 'max:255', Rule::unique(User::tableName(), 'email')->ignore($userId, 'email')],
            'password' => ['required', 'string', 'max:255', 'min:8', 'same:password'],
            'confirm_password' => ['required', 'string', 'max:255', 'min:8'],
        ];
    }
}
