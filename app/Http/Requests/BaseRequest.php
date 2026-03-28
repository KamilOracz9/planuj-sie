<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;

class BaseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if ((new $this->modelClass)->isSluggable) {
            if (in_array((new $this->modelClass)->sluggable, (new $this->modelClass)->translatable)) {
                $names = $this->input('name', []);

                $this->merge([
                    'slug' => array_map(fn($item) => Str::slug($item), $names),
                ]);
            } else {
                $name = $this->input((new $this->modelClass)->sluggable);

                $this->merge([
                    'slug' => Str::slug($name),
                ]);
            }
        }

        parent::prepareForValidation();
    }

    protected function failedValidation(Validator $validator): void
    {
        throw new \Illuminate\Http\Exceptions\HttpResponseException(
            response()->json([
                'message' => 'Validation failed',
                'errors'  => $validator->errors(),
            ], 422)
        );
    }
}
