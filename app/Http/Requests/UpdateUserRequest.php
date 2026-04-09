<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Validation\Rule;

class UpdateUserRequest extends BaseRequest
{
    protected string $modelClass = User::class;

    public function rules(): array
    {
        $userId = $this->route('id');

        return [
            'name' => ['required', 'string', 'max:255', Rule::unique(User::tableName(), 'name')->ignore($userId, 'id')],
            'email' => ['required', 'email', 'max:255', Rule::unique(User::tableName(), 'email')->ignore($userId, 'id')],
        ];
    }
}
