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
        Schema::table('anak', function (Blueprint $table) {
            $table->enum('jenis_kelamin', ['Laki-laki', 'Perempuan'])
                  ->after('nama')   // letakkan setelah kolom nama
                  ->nullable(false)
                  ->default('Laki-laki'); // kasih default supaya tidak error di sqlite/mysql lama
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('anak', function (Blueprint $table) {
            $table->dropColumn('jenis_kelamin');
        });
    }
};
