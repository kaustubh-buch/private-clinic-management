<?php

namespace Database\Seeders;

use App\Models\TemplateCategory;
use Illuminate\Database\Seeder;

class TemplateCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $templateCategories = config('constants.TEMPLATE_CATEGORY');
        foreach ($templateCategories as $row) {
            TemplateCategory::updateOrCreate(
                ['id' => $row['id']],
                ['name' => $row['name']]
            );
        }
    }
}
