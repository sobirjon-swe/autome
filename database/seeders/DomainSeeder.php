<?php

namespace Database\Seeders;

use App\Models\Domain;
use Illuminate\Database\Seeder;

class DomainSeeder extends Seeder
{
    public function run(): void
    {
        $domains = [
            ['name' => 'Tillar',      'slug' => 'languages',   'icon' => '🌍', 'sort_order' => 1],
            ['name' => 'Dasturlash',  'slug' => 'programming', 'icon' => '💻', 'sort_order' => 2],
            ['name' => 'Dizayn',      'slug' => 'design',      'icon' => '🎨', 'sort_order' => 3],
            ['name' => 'Musiqa',      'slug' => 'music',       'icon' => '🎵', 'sort_order' => 4],
            ['name' => 'Matematika',  'slug' => 'math',        'icon' => '📐', 'sort_order' => 5],
            ['name' => 'Biznes',      'slug' => 'business',    'icon' => '💼', 'sort_order' => 6],
        ];

        foreach ($domains as $domain) {
            Domain::create(array_merge($domain, ['is_system' => true]));
        }
    }
}
