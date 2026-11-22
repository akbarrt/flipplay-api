<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Media extends Model
{
    use HasFactory;

    // Eksplisit karena "Media" adalah kata uncountable
    protected $table = 'media';

    protected $fillable = ['title', 'type', 'genre', 'release_year', 'description'];

    protected $casts = [
        'release_year' => 'integer',
    ];

    // Relasi many-to-many: satu media dikoleksi banyak pengguna
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'media_user')->withTimestamps();
    }

    // Query scope: pencarian berdasarkan judul (?search=...)
    public function scopeSearch($query, ?string $term)
    {
        return $term ? $query->where('title', 'like', "%{$term}%") : $query;
    }
}
