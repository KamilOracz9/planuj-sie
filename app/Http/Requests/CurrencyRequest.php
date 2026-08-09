<?php

namespace App\Http\Requests;

use App\Models\Currency;
use Illuminate\Validation\Rule;

class CurrencyRequest extends BaseRequest
{
    protected string $modelClass = Currency::class;

    public function rules(): array
    {
        $currencyId = $this->route('id');

        return [
            'code' => ['required', 'string', 'size:3', 'uppercase', Rule::unique(Currency::tableName(), 'code')->ignore($currencyId)],
            'name' => ['required', 'string', 'max:255'],
            'symbol' => ['required', 'string', 'max:8'],
            'decimal_places' => ['required', 'integer', 'min:0', 'max:6'],
        ];
    }
}
