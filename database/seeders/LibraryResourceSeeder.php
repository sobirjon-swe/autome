<?php

namespace Database\Seeders;

use App\Models\LibraryResource;
use Illuminate\Database\Seeder;

class LibraryResourceSeeder extends Seeder
{
    public function run(): void
    {
        $resources = [
            [
                'title'        => 'Grammar in Use Elementary',
                'type'         => 'book',
                'subject_name' => 'Ingliz tili',
                'domain_slug'  => 'languages',
                'author'       => 'Raymond Murphy',
                'total_units'  => 115,
                'description'  => 'Boshlang\'ich darajadagi ingliz tili grammatikasi',
                'is_approved'  => true,
            ],
            [
                'title'        => 'Grammar in Use Intermediate',
                'type'         => 'book',
                'subject_name' => 'Ingliz tili',
                'domain_slug'  => 'languages',
                'author'       => 'Raymond Murphy',
                'total_units'  => 128,
                'description'  => 'O\'rta darajadagi ingliz tili grammatikasi',
                'is_approved'  => true,
            ],
            [
                'title'        => 'CS50: Introduction to Computer Science',
                'type'         => 'course',
                'subject_name' => 'Computer Science',
                'domain_slug'  => 'programming',
                'author'       => 'Harvard University',
                'total_units'  => null,
                'description'  => 'Harvard universitetining mashhur kompyuter fanlari kursi',
                'url'          => 'https://cs50.harvard.edu',
                'is_approved'  => true,
            ],
            [
                'title'        => 'Python Crash Course',
                'type'         => 'book',
                'subject_name' => 'Python',
                'domain_slug'  => 'programming',
                'author'       => 'Eric Matthes',
                'total_units'  => 20,
                'description'  => 'Python dasturlash tili bo\'yicha amaliy qo\'llanma',
                'is_approved'  => true,
            ],
            [
                'title'        => 'Laravel Documentation',
                'type'         => 'article',
                'subject_name' => 'Laravel',
                'domain_slug'  => 'programming',
                'author'       => 'Taylor Otwell',
                'total_units'  => null,
                'description'  => 'Laravel framework rasmiy hujjatlari',
                'url'          => 'https://laravel.com/docs',
                'is_approved'  => true,
            ],
            [
                'title'        => 'JavaScript.info',
                'type'         => 'article',
                'subject_name' => 'JavaScript',
                'domain_slug'  => 'programming',
                'author'       => 'Ilya Kantor',
                'total_units'  => null,
                'description'  => 'JavaScript bo\'yicha to\'liq qo\'llanma',
                'url'          => 'https://javascript.info',
                'is_approved'  => true,
            ],
            [
                'title'        => 'BBC 6 Minute English',
                'type'         => 'podcast',
                'subject_name' => 'Ingliz tili',
                'domain_slug'  => 'languages',
                'author'       => 'BBC Learning English',
                'total_units'  => null,
                'description'  => 'BBC\'ning ingliz tili o\'rganish podkasti',
                'url'          => 'https://www.bbc.co.uk/learningenglish/english/features/6-minute-english',
                'is_approved'  => true,
            ],
            [
                'title'        => 'Vocabulary in Use',
                'type'         => 'book',
                'subject_name' => 'Ingliz tili',
                'domain_slug'  => 'languages',
                'author'       => 'Michael McCarthy',
                'total_units'  => 60,
                'description'  => 'Ingliz tili lug\'ati bo\'yicha mashqlar to\'plami',
                'is_approved'  => true,
            ],
        ];

        foreach ($resources as $resource) {
            LibraryResource::create($resource);
        }
    }
}
