<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MediaResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'collection_name' => $this->collection_name,
            'channel_id' => $this->channel_id,
            'name' => $this->name,
            'file_name' => $this->file_name,
            'mime_type' => $this->mime_type,
            'size' => $this->size,
            'order_column' => $this->order_column,
            'folder_id' => $this->getCustomProperty('folder_id'),
            // Media lives on the private 'media' disk (see the api-security
            // skill) - served only through this authenticated route, never
            // a direct disk URL.
            'url' => route('media.show', ['media' => $this->id]),
            'conversions' => collect($this->getGeneratedConversions())
                ->filter()
                ->keys()
                ->mapWithKeys(fn(string $name) => [$name => route('media.show', ['media' => $this->id, 'conversion' => $name])]),
            'created_at' => $this->created_at,
        ];
    }
}
