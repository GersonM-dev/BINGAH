<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Anak extends Model
{
    use HasFactory;

    protected $table = 'anak';

    protected $fillable = [
        'nama',
        'id_user',
        'tanggal_lahir',
        'jenis_kelamin',
        'nama_ayah',
        'nama_ibu',
        'alamat',
    ];

    // Relasi ke Data Antropometry
    public function dataAntropometries()
    {
        return $this->hasMany(DataAntropometry::class, 'id_anak');
    }

    protected static function booted(): void
    {
        static::creating(function ($model) {
            $model->id_user ??= auth()->id();
        });
    }
}
