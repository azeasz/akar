<?php

return [
    /*
    |--------------------------------------------------------------------------
    | TinyMCE API Key
    |--------------------------------------------------------------------------
    |
    | This is your TinyMCE API key for Cloud deployment.
    | Buat API key gratis di https://www.tiny.cloud/
    |
    */
    'api_key' => env('TINYMCE_API_KEY', ''),

    /*
    |--------------------------------------------------------------------------
    | TinyMCE Default Config
    |--------------------------------------------------------------------------
    |
    | Konfigurasi default untuk semua instance TinyMCE
    |
    */
    'default_config' => [
        'height' => 400,
        'menubar' => true,
        'plugins' => [
            'advlist', 'autolink', 'lists', 'link', 'image', 'charmap', 'preview',
            'anchor', 'searchreplace', 'visualblocks', 'code', 'fullscreen',
            'insertdatetime', 'media', 'table', 'help', 'wordcount'
        ],
        'toolbar' => 'undo redo | blocks | bold italic backcolor | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | removeformat | help',
        'content_style' => 'body { font-family:Helvetica,Arial,sans-serif; font-size:14px }'
    ],
]; 