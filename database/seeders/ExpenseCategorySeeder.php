<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ExpenseCategory;

class ExpenseCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Tovar xaridi', 'sort_order' => 1],
            ['name' => 'Xom ashyo', 'sort_order' => 2],
            ['name' => 'Ish haqi', 'sort_order' => 3],
            ['name' => 'Elektr energiya', 'sort_order' => 4],
            ['name' => 'Transport', 'sort_order' => 5],
            ['name' => 'Jihozlar ta\'miri', 'sort_order' => 6],
            ['name' => 'Bir martalik idishlar', 'sort_order' => 7],
            ['name' => 'Boshqa', 'sort_order' => 8],
        ];

        foreach ($categories as $category) {
            ExpenseCategory::firstOrCreate(
                ['name' => $category['name']],
                ['sort_order' => $category['sort_order'], 'is_active' => true]
            );
        }

        $this->command->info('Expense categories seeded.');
    }
}
