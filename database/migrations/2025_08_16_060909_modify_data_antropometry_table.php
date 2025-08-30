<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('data_antropometry', function (Blueprint $table) {
            // Ubah nama kolom
            $table->renameColumn('lingkar_lengan_bawah', 'lingkar_kepala');

            // Tambah kolom tanggal_ukur
            $table->date('tanggal_ukur')->after('id_user');
        });
    }

    public function down(): void
    {
        Schema::table('data_antropometry', function (Blueprint $table) {
            // Balikkan perubahan kalau di-rollback
            $table->renameColumn('lingkar_kepala', 'lingkar_lengan_bawah');
            $table->dropColumn('tanggal_ukur');
        });
    }
};
