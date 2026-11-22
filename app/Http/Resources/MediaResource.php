<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MediaResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'type' => $this->type,
            'genre' => $this->genre,
            'release_year' => $this->release_year,
            'description' => $this->description,
            // Hanya muncul jika withCount('users') dipakai
            'users_count' => $this->whenCounted('users'),
            // Hanya muncul jika relasi users sudah di-load (eager loading)
            'users' => UserResource::collection($this->whenLoaded('users')),
            // Hanya muncul saat media diambil lewat relasi pivot (koleksi pengguna)
            'added_at' => $this->whenPivotLoaded('media_user', fn () => $this->pivot->created_at),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
