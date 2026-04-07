<?php

namespace App\Http\Requests;

use App\Models\Locale;
use Illuminate\Validation\Rule;

class LocaleRequest extends BaseRequest
{
    protected string $modelClass = Locale::class;

    public function rules(): array
    {
        $localeId = $this->route('id');

        return [
            'name' => ['required', 'array'],
            'name.pl-PL' => ['required', 'string', 'max:255'],
            'name.*' => ['nullable', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:255', Rule::unique('locales', 'code')->ignore($localeId, 'id')],
        ];
    }
}
