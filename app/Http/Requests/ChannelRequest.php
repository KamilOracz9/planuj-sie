<?php

namespace App\Http\Requests;

use App\Models\Channel;
use Illuminate\Validation\Rule;

class ChannelRequest extends BaseRequest
{
    protected string $modelClass = Channel::class;

    public function rules(): array
    {
        $channelId = $this->route('id');

        return [
            'name' => ['required', 'array'],
            'name.pl-PL' => ['required', 'string', 'max:255'],
            'name.*' => ['nullable', 'string', 'max:255'],
            'slug' => ['required', 'array'],
            'slug.pl-PL' => ['required', 'string', 'max:255'],
            'slug.*' => ['nullable', 'string', 'max:255', Rule::unique('channel_translations', 'slug')->ignore($channelId, 'channel_id')],
        ];
    }
}
