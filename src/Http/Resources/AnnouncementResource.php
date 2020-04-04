<?php

namespace Vanguard\Announcements\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Vanguard\Http\Resources\UserResource;

class AnnouncementResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => (int) $this->id,
            'user_id' => (int) $this->user_id,
            'title' => $this->title,
            'body' => $this->body,
            'parsed_body' => (string) $this->parsed_body,
            'created_at' => (string) $this->created_at,
            'updated_at' => (string) $this->updated_at,
            'user' => new UserResource($this->whenLoaded('creator')),
        ];
    }
}
