<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreMediaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $presence = $this->isMethod('PATCH') ? 'sometimes' : 'required';

        return [
            // ignore() agar saat PATCH judul milik media itu sendiri tidak dianggap duplikat
            'title' => [$presence, 'string', 'max:255',
                Rule::unique('media', 'title')->ignore($this->route('media'))],
            'type' => [$presence, 'string', Rule::in(['film', 'series', 'music', 'game'])],
            'genre' => [$presence, 'string', 'max:100'],
            'release_year' => ['sometimes', 'nullable', 'integer', 'between:1888,2100'],
            'description' => ['sometimes', 'nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'Judul media wajib diisi',
            'title.unique' => 'Judul media sudah ada, gunakan judul lain',
            'type.required' => 'Jenis media wajib diisi',
            'type.in' => 'Jenis media harus salah satu dari: film, series, music, game',
            'genre.required' => 'Genre wajib diisi',
            'release_year.between' => 'Tahun rilis tidak valid',
        ];
    }
}
