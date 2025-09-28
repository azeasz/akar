<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class UpdateVerifiedUsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Update semua user yang status=1 tapi email_verified_at masih NULL
        DB::table('users')
            ->whereNull('email_verified_at')
            ->where('status', 1)
            ->update(['email_verified_at' => Carbon::now()]);
            
        $this->command->info('Email verification timestamps have been added to all active users.');
    }
} 