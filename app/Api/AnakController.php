<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Anak;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

/**
 * REST API controller for managing Anak records.
 */
class AnakController extends Controller
{
    public function index()
    {
        // Menampilkan daftar anak dengan relasi antropometri & prediksi secara paginasi
        $anaks = Anak::with('dataAntropometries.prediksi')->paginate(20);
        return response()->json($anaks);
    }

    public function store(Request $request)
    {
        // Validasi payload
        $validator = Validator::make($request->all(), [
            'nama'           => 'required|string|max:150',
            'tanggal_lahir'  => 'required|date',
            'jenis_kelamin'  => 'required|string|in:Laki-Laki,Perempuan',
            'nama_ayah'      => 'required|string|max:150',
            'nama_ibu'       => 'required|string|max:150',
            'alamat'         => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Membuat data anak dengan id_user diambil dari pengguna terautentikasi (jika ada)
        $anak = Anak::create([
            'nama'          => $request->nama,
            'tanggal_lahir' => $request->tanggal_lahir,
            'jenis_kelamin' => $request->jenis_kelamin,
            'nama_ayah'     => $request->nama_ayah,
            'nama_ibu'      => $request->nama_ibu,
            'alamat'        => $request->alamat,
            'id_user'       => optional($request->user())->id,
        ]);

        return response()->json($anak->load('dataAntropometries.prediksi'), 201);
    }

    public function show(Anak $anak)
    {
        $anak->load('dataAntropometries.prediksi');
        return response()->json($anak);
    }

    public function update(Request $request, Anak $anak)
    {
        $validator = Validator::make($request->all(), [
            'nama'           => 'sometimes|required|string|max:150',
            'tanggal_lahir'  => 'sometimes|required|date',
            'jenis_kelamin'  => 'sometimes|required|string|in:Laki-Laki,Perempuan',
            'nama_ayah'      => 'sometimes|required|string|max:150',
            'nama_ibu'       => 'sometimes|required|string|max:150',
            'alamat'         => 'sometimes|required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $anak->update($request->only(['nama', 'tanggal_lahir', 'jenis_kelamin', 'nama_ayah', 'nama_ibu', 'alamat']));
        return response()->json($anak->load('dataAntropometries.prediksi'));
    }

    public function destroy(Anak $anak)
    {
        $anak->delete();
        return response()->json(['message' => 'deleted']);
    }
}
