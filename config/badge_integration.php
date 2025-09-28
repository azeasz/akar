<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Badge Integration Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for badge management integration with main FOBi database
    |
    */

    'database' => [
        'connection' => env('BADGE_DB_CONNECTION', 'mysql'),
        'table' => 'badges',
        'badge_types_table' => 'badge_types',
    ],

    'upload' => [
        'path' => 'uploads/badges',
        'max_size' => 2048, // KB
        'allowed_types' => ['jpeg', 'png', 'jpg', 'gif', 'svg'],
    ],

    'pagination' => [
        'per_page' => 15,
        'range' => 2, // Show current ± 2 pages
    ],

    'validation' => [
        'title_max_length' => 255,
        'text_max_length' => 500,
        'total_min' => 1,
        'total_max' => 10000,
    ],

    'application' => [
        'name' => 'akar',
        'display_name' => 'Akar',
        'icon' => 'bi-tree',
    ],

    'features' => [
        'rich_text_editor' => true,
        'file_upload' => true,
        'advanced_filtering' => true,
        'bulk_actions' => false, // Not implemented yet
    ],
];
