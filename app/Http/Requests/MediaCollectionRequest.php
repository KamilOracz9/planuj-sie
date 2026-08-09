<?php

namespace App\Http\Requests;

use App\Models\Channel;
use App\Models\MediaCollection;
use Illuminate\Validation\Rule;

class MediaCollectionRequest extends BaseRequest
{
    protected string $modelClass = MediaCollection::class;

    public function rules(): array
    {
        $mediaCollectionId = $this->route('id');

        return [
            'code' => ['required', 'string', 'max:255', 'alpha_dash', Rule::unique(MediaCollection::tableName(), 'code')->ignore($mediaCollectionId)],
            'name' => ['required', 'string', 'max:255'],
            'kind' => ['required', Rule::in(['image', 'document'])],
            'type' => ['required', Rule::in(['single', 'multiple'])],
            'conversions' => ['nullable', 'array', function ($attribute, $value, $fail) {
                $pairs = array_map(fn($c) => ($c['channel_id'] ?? null) . '-' . ($c['name'] ?? null), $value ?? []);
                if (count($pairs) !== count(array_unique($pairs))) {
                    $fail('Duplicate channel/name conversion rows are not allowed.');
                }
            }],
            'conversions.*.channel_id' => ['required', 'integer', Rule::exists(Channel::tableName(), 'id')],
            'conversions.*.name' => ['required', 'string', 'max:255'],
            'conversions.*.width' => ['required', 'integer', 'min:1'],
            'conversions.*.height' => ['required', 'integer', 'min:1'],
            'conversions.*.fit' => ['required', Rule::in(['crop', 'contain'])],
            'assignments' => ['nullable', 'array', function ($attribute, $value, $fail) {
                $pairs = array_map(fn($a) => ($a['channel_id'] ?? null) . '-' . ($a['model_type'] ?? null), $value ?? []);
                if (count($pairs) !== count(array_unique($pairs))) {
                    $fail('Duplicate channel/model type assignment rows are not allowed.');
                }
            }],
            'assignments.*.channel_id' => ['required', 'integer', Rule::exists(Channel::tableName(), 'id')],
            'assignments.*.model_type' => ['required', 'string', Rule::in(array_keys(config('media.model_types')))],
        ];
    }
}
