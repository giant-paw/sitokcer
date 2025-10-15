<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\DistribusiTahunan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DistribusiTahunanController extends Controller
{
    public function index(Request $request)
    {
        // --- 1. Query Utama untuk Tabel Data ---
        $query = DistribusiTahunan::query();

        // Filter berdasarkan tab "Nama Kegiatan"
        if ($request->filled('kegiatan')) {
            $query->where('nama_kegiatan', $request->kegiatan);
        }

        // Filter berdasarkan tahun
        if ($request->filled('tahun')) {
            $query->where('tahun_kegiatan', $request->tahun);
        }

        // Filter berdasarkan pencarian
        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $query->where(function($q) use ($searchTerm) {
                $q->where('blok_sensus_responden', 'like', "%{$searchTerm}%")
                  ->orWhere('pencacah', 'like', "%{$searchTerm}%")
                  ->orWhere('pengawas', 'like', "%{$searchTerm}%");
            });
        }
        
        $listData = $query->latest()->paginate(20)->withQueryString();

        // --- 2. Query untuk Data Tab di Atas (dengan jumlah) ---
        $kegiatanCounts = DistribusiTahunan::query()
            ->select('nama_kegiatan', DB::raw('count(*) as total'))
            ->groupBy('nama_kegiatan')
            ->orderBy('nama_kegiatan')
            ->get();
        
        // Mengarahkan ke view sesuai struktur folder Anda
        return view('timDistribusi.distribusitahunan', compact('listData', 'kegiatanCounts'));
    }

    /**
     * Menyimpan data baru yang diinput dari form.
     */
    public function store(Request $request)
    {
        // Validasi input sesuai form
        $validatedData = $request->validate([
            'nama_kegiatan' => 'required|string|max:255',
            'blok_sensus_responden' => 'required|string|max:255',
            'pencacah' => 'required|string|max:255',
            'pengawas' => 'required|string|max:255',
            'target_penyelesaian' => 'required|date_format:d/m/Y',
            'flag_progress' => 'required|string',
        ]);

        // Tambahkan tahun_kegiatan secara otomatis dari target_penyelesaian
        $validatedData['tahun_kegiatan'] = \Carbon\Carbon::createFromFormat('d/m/Y', $request->target_penyelesaian)->year;
        
        DistribusiTahunan::create($validatedData);

        return back()->with('success', 'Data berhasil ditambahkan!');
    }
}
