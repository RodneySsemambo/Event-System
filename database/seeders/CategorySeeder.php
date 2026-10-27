<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Catering',    'slug' => 'catering'],
            ['name' => 'Decoration',  'slug' => 'decoration'],
            ['name' => 'Photography', 'slug' => 'photography'],
            ['name' => 'Videography', 'slug' => 'videography'],
            ['name' => 'DJ',          'slug' => 'dj'],
            ['name' => 'MC',          'slug' => 'mc'],
            ['name' => 'Venue',       'slug' => 'venue'],
            ['name' => 'Live Band',   'slug' => 'live-band'],
        ];

        foreach ($categories as $c) {
            DB::table('categories')->updateOrInsert(['slug' => $c['slug']], $c);
        }
    }
}