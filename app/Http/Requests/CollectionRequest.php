<?php

namespace App\Http\Requests;

use App\Models\Channel;
use App\Models\Collection;
use Illuminate\Validation\Rule;

class CollectionRequest extends BaseRequest
{
    protected string $modelClass = Collection::class;

    public function rules(): array
    {
        $collectionId = $this->route('id');

        return [
            'name' => ['required', 'array'],
            'name.pl-PL' => ['required', 'string', 'max:255'],
            'name.*' => ['nullable', 'string', 'max:255'],
            'slug' => ['required', 'array'],
            'slug.pl-PL' => ['required', 'string', 'max:255'],
            'slug.*' => ['nullable', 'string', 'max:255', Rule::unique('collection_translations', 'slug')->ignore($collectionId, 'collection_id')],
            'attributes' => ['nullable', 'array'],
            'attributes.*.attribute_id' => ['required', 'integer', 'exists:attributes,id'],
            'attributes.*.data' => ['required'],
            'channels' => ['nullable', 'array'],
            'channels.*.channel_id' => ['required', 'integer', Rule::exists(Channel::tableName(), 'id')],
            'channels.*.is_enabled' => ['required', 'boolean'],
        ];
    }
}
