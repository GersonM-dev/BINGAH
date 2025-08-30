<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Tabel Anak
        Schema::create('anak', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->date('tanggal_lahir');
            $table->string('nama_ayah');
            $table->string('nama_ibu');
            $table->text('alamat')->nullable();
            $table->timestamps();
        });

        // Tabel Data Antropometry
        Schema::create('data_antropometry', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_anak')->constrained('anak')->onDelete('cascade');
            $table->foreignId('id_user')->constrained('users')->onDelete('cascade');
            $table->integer('umur_bulan');
            $table->enum('tipe_ukur', ['berdiri', 'telentang']);
            $table->float('tinggi');
            $table->float('berat');
            $table->float('lingkar_lengan_atas')->nullable();
            $table->float('lingkar_lengan_bawah')->nullable();
            $table->timestamps();
        });

        // Tabel Prediksi
        Schema::create('prediksi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_dataAntropometry')->constrained('data_antropometry')->onDelete('cascade');
            $table->string('status_tbu')->nullable();
            $table->string('status_bbu')->nullable();
            $table->string('status_tbbb')->nullable();
            $table->text('rekomendasi')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('prediksi');
        Schema::dropIfExists('data_antropometry');
        Schema::dropIfExists('anak');
    }
};
