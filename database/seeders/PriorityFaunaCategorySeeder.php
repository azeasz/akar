<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PriorityFaunaCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'name' => 'CR',
                'type' => 'iucn',
                'description' => 'Critically Endangered - IUCN Red List',
                'color_code' => '#dc3545',
                'is_active' => true,
            ],
            [
                'name' => 'EN',
                'type' => 'iucn',
                'description' => 'Endangered - IUCN Red List',
                'color_code' => '#fd7e14',
                'is_active' => true,
            ],
            [
                'name' => 'VU',
                'type' => 'iucn',
                'description' => 'Vulnerable - IUCN Red List',
                'color_code' => '#ffc107',
                'is_active' => true,
            ],
            [
                'name' => 'Dilindungi',
                'type' => 'protection_status',
                'description' => 'Dalam Status Perlindungan',
                'color_code' => '#198754',
                'is_active' => true,
            ],
            [
                'name' => 'Tidak Dilindungi',
                'type' => 'protection_status',
                'description' => 'Tidak Dalam Status Perlindungan',
                'color_code' => '#6c757d',
                'is_active' => true,
            ],
        ];

        foreach ($categories as $category) {
            DB::table('priority_fauna_categories')->updateOrInsert(
                ['name' => $category['name'], 'type' => $category['type']],
                array_merge($category, [
                    'created_at' => now(),
                    'updated_at' => now(),
                ])
            );
        }
    }
}
