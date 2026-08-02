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
            'name' => $this->name,
            'file_name' => $this->file_name,
            'mime_type' => $this->mime_type,
            'size' => $this->size,
            'order_column' => $this->order_column,
            'folder_id' => $this->getCustomProperty('folder_id'),
            'url' => $this->getUrl(),
            'conversions' => collect($this->getGeneratedConversions())
                ->filter()
                ->keys()
                ->mapWithKeys(fn(string $name) => [$name => $this->getUrl($name)]),
            'created_at' => $this->created_at,
        ];
    }
}
