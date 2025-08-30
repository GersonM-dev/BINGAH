<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Prediksi extends Model
{
    use HasFactory;

    protected $table = 'prediksi';

    protected $fillable = [
        'id_dataAntropometry',
        'status_tbu',
        'status_bbu',
        'status_tbbb',
        'rekomendasi',
    ];

    // Relasi ke Data Antropometry
    public function dataAntropometry()
    {
        return $this->belongsTo(DataAntropometry::class, 'id_dataAntropometry');
    }
}
