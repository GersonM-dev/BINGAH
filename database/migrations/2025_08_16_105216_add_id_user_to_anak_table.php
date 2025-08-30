<?php

// database/migrations/xxxx_xx_xx_xxxxxx_add_user_to_anak_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('anak', function (Blueprint $table) {
            $table->foreignId('id_user')
                ->after('id')
                ->constrained('users')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('anak', function (Blueprint $table) {
            $table->dropConstrainedForeignId('id_user');
        });
    }
};

