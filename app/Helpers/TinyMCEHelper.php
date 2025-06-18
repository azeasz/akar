<?php

if (!function_exists('tinymce_url')) {
    /**
     * Generate TinyMCE URL with API key
     * 
     * @return string
     */
    function tinymce_url()
    {
        return 'https://cdn.tiny.cloud/1/' . config('tinymce.api_key', '') . '/tinymce/6/tinymce.min.js';
    }
} 