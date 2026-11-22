<?php

namespace Database\Seeders;

use App\Models\Media;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::factory()->create(['name' => 'Pengguna Demo', 'email' => 'demo@flipplay.test']);
        User::factory(4)->create();

        $media = Media::factory(15)->create();

        // Isi koleksi awal: tiap pengguna mengoleksi 3 media acak
        User::all()->each(fn (User $user) => $user->media()->attach($media->random(3)->pluck('id')));
    }
}
