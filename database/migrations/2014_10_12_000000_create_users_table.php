<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('username')->unique();
            $table->string('name');
            $table->string('firstname')->nullable();
            $table->string('lastname')->nullable();
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->text('reason')->nullable();
            $table->string('alias_name')->nullable();
            $table->string('organisasi')->nullable();
            $table->string('phone_number')->nullable();
            $table->string('social_media')->nullable();
            $table->string('profile_picture')->nullable();
            $table->tinyInteger('level')->default(1)->comment('1=user, 2=admin');
            
            // Kolom-kolom dari struktur lama untuk kompatibilitas
            $table->string('avatar')->nullable(); // field avatar lama
            $table->string('domisili')->nullable();
            $table->text('pengamatan_satwa')->nullable();
            $table->string('phone')->nullable(); // field phone lama
            $table->tinyInteger('status')->default(0); // field status lama
            
            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();

            $table->index('username');
            $table->index('phone_number');
            $table->index('phone');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
