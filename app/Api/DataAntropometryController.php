<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Anak;
use App\Models\DataAntropometry;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

/**
 * REST API controller for managing anthropometric measurements.
 */
class DataAntropometryController extends Controller
{
    public function index(Anak $anak)
    {
        $items = $anak->dataAntropometries()->with('prediksi')->paginate(20);
        return response()->json($items);
    }

    public function store(Request $request, Anak $anak)
    {
        $validator = Validator::make($request->all(), [
            'tanggal_ukur'        => 'required|date',
            'tipe_ukur'           => 'required|in:berdiri,telentang',
            'tinggi'              => 'required|numeric|min:0|max:200',
            'berat'               => 'required|numeric|min:0|max:200',
            'lingkar_lengan_atas' => 'required|numeric|min:0',
            'lingkar_kepala'      => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Hitung umur bulan dari tanggal lahir
        $umur_bulan = null;
        if ($anak->tanggal_lahir) {
            $birth   = Carbon::parse($anak->tanggal_lahir);
            $measure = Carbon::parse($request->tanggal_ukur);
            $umur_bulan = $birth->diffInMonths($measure);
        }

        $data = $anak->dataAntropometries()->create([
            'tanggal_ukur'        => $request->tanggal_ukur,
            'umur_bulan'          => $umur_bulan,
            'tipe_ukur'           => $request->tipe_ukur,
            'tinggi'              => $request->tinggi,
            'berat'               => $request->berat,
            'lingkar_lengan_atas' => $request->lingkar_lengan_atas,
            'lingkar_kepala'      => $request->lingkar_kepala,
            'id_user'             => optional($request->user())->id ?? $anak->id_user,
        ]);

        // Relasi prediksi akan terisi melalui observer pada model DataAntropometry
        $data->load('prediksi');

        return response()->json($data, 201);
    }

    public function show(DataAntropometry $antropometry)
    {
        $antropometry->load('prediksi', 'anak');
        return response()->json($antropometry);
    }

    public function update(Request $request, DataAntropometry $antropometry)
    {
        $validator = Validator::make($request->all(), [
            'tanggal_ukur'        => 'sometimes|required|date',
            'tipe_ukur'           => 'sometimes|required|in:berdiri,telentang',
            'tinggi'              => 'sometimes|required|numeric|min:0|max:200',
            'berat'               => 'sometimes|required|numeric|min:0|max:200',
            'lingkar_lengan_atas' => 'sometimes|required|numeric|min:0',
            'lingkar_kepala'      => 'sometimes|required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $antropometry->fill($request->only([
            'tanggal_ukur',
            'tipe_ukur',
            'tinggi',
            'berat',
            'lingkar_lengan_atas',
            'lingkar_kepala',
        ]));

        // Recalculate age in months if the date changed
        if ($request->filled('tanggal_ukur')) {
            $birthDate = $antropometry->anak?->tanggal_lahir;
            if ($birthDate) {
                $birth   = Carbon::parse($birthDate);
                $measure = Carbon::parse($request->tanggal_ukur);
                $antropometry->umur_bulan = $birth->diffInMonths($measure);
            }
        }

        $antropometry->save();
        $antropometry->load('prediksi');

        return response()->json($antropometry);
    }

    public function destroy(DataAntropometry $antropometry)
    {
        $antropometry->delete();
        return response()->json(['message' => 'deleted']);
    }
}
