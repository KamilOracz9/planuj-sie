<?php

namespace App\Http\Requests;

use App\Models\Channel;
use App\Models\Series;
use Illuminate\Validation\Rule;

class SeriesRequest extends BaseRequest
{
    protected string $modelClass = Series::class;

    public function rules(): array
    {
        $seriesId = $this->route('id');

        return [
            'name' => ['required', 'array'],
            'name.pl-PL' => ['required', 'string', 'max:255'],
            'name.*' => ['nullable', 'string', 'max:255'],
            'slug' => ['required', 'array'],
            'slug.pl-PL' => ['required', 'string', 'max:255'],
            'slug.*' => ['nullable', 'string', 'max:255', Rule::unique('series_translations', 'slug')->ignore($seriesId, 'series_id')],
            'attributes' => ['nullable', 'array'],
            'attributes.*.attribute_id' => ['required', 'integer', 'exists:attributes,id'],
            'attributes.*.data' => ['required'],
            'channels' => ['nullable', 'array'],
            'channels.*.channel_id' => ['required', 'integer', Rule::exists(Channel::tableName(), 'id')],
            'channels.*.is_enabled' => ['required', 'boolean'],
        ];
    }
}
