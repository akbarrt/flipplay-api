<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreMediaRequest;
use App\Http\Resources\MediaResource;
use App\Models\Media;
use Illuminate\Http\Request;

class MediaController extends Controller
{
    // GET /api/media?search=&type=&genre=&page=
    public function index(Request $request)
    {
        $media = Media::withCount('users')
            ->search($request->query('search'))
            ->when($request->filled('type'), fn ($q) => $q->where('type', $request->query('type')))
            ->when($request->filled('genre'), fn ($q) => $q->where('genre', $request->query('genre')))
            ->latest()
            ->paginate(10);

        return MediaResource::collection($media);
    }

    // GET /api/media/{media}  (Route Model Binding -> otomatis 404 jika tidak ada)
    public function show(Media $media)
    {
        $media->load('users')->loadCount('users');

        return new MediaResource($media);
    }

    // POST /api/media  -> 201 Created
    public function store(StoreMediaRequest $request)
    {
        $media = Media::create($request->validated());

        return (new MediaResource($media))->response()->setStatusCode(201);
    }

    // PATCH /api/media/{media}
    public function update(StoreMediaRequest $request, Media $media)
    {
        $media->update($request->validated());

        return new MediaResource($media);
    }

    // DELETE /api/media/{media}  (baris pivot ikut terhapus via cascadeOnDelete)
    public function destroy(Media $media)
    {
        $media->delete();

        return response()->json(['message' => 'Media berhasil dihapus']);
    }
}
