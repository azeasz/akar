<?php

use App\Models\ActivityLog;

if (!function_exists('activity_log')) {
    /**
     * Log aktivitas pengguna
     * 
     * @param string $action Jenis aktivitas
     * @param string $description Deskripsi aktivitas
     * @param int|null $userId ID pengguna yang melakukan aktivitas
     * @return void
     */
    function activity_log(string $action, string $description, int $userId = null)
    {
        ActivityLog::create([
            'user_id' => $userId ?? auth()->id(),
            'action' => $action,
            'description' => $description,
        ]);
    }
} 