<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PencacahController extends Controller
{
    public function index(Request $request)
    {
        $q = $request->input('q');

        // Daftar tabel yang akan digabungkan
        $tables = [
            'distribusi_bulanan', 'produksi_bulanan', 'produksi_tahunan',
            'produksi_triwulanan', 'sosial_semesteran', 'sosial_tahunan', 'sosial_triwulanan'
        ];

        // Membangun query UNION hanya untuk kolom 'pencacah'
        $firstTable = array_shift($tables);
        $unionQuery = DB::table($firstTable)->select('pencacah');

        foreach ($tables as $table) {
            $unionQuery->unionAll(DB::table($table)->select('pencacah'));
        }

        // Query utama yang menggunakan subquery dari UNION di atas
        $query = DB::query()->fromSub($unionQuery, 'data_gabungan')
            ->select(
                'pencacah as nama_pencacah',
                DB::raw('COUNT(*) as total_responden')
            )
            ->whereNotNull('pencacah')
            ->where('pencacah', '!=', '');

        // Terapkan filter pencarian jika ada
        if ($q) {
            // LOGIKA BARU: Mencari nama pencacah yang berawalan dengan huruf yang diinput
            $query->where('pencacah', 'like', "{$q}%");
        }
        
        // Grouping, ordering, dan pagination
        $rekapPencacah = $query->groupBy('pencacah')
            ->orderBy('nama_pencacah', 'asc')
            ->paginate(10) // Menampilkan 10 item per halaman
            ->withQueryString(); // Agar parameter search terbawa saat pindah halaman

        return view('Rekapitulasi.pencacah.index', [
            'rekapPencacah' => $rekapPencacah,
            'q' => $q // Kirim variabel search ke view
        ]);
    }

    public function getDetailKegiatan($nama)
    {
        $namaPencacah = urldecode($nama);

        // Query ini sudah benar, tidak perlu diubah.
        $sqlDetail = "SELECT nama_kegiatan, COUNT(BS_Responden) as jumlah_responden FROM (
                            SELECT nama_kegiatan, BS_Responden FROM distribusi_bulanan WHERE pencacah = ?
                            UNION ALL
                            SELECT nama_kegiatan, BS_Responden FROM produksi_bulanan WHERE pencacah = ?
                            UNION ALL
                            SELECT nama_kegiatan, BS_Responden FROM produksi_tahunan WHERE pencacah = ?
                            UNION ALL
                            SELECT nama_kegiatan, BS_Responden FROM produksi_triwulanan WHERE pencacah = ?
                            UNION ALL
                            SELECT nama_kegiatan, BS_Responden FROM sosial_semesteran WHERE pencacah = ?
                            UNION ALL
                            SELECT nama_kegiatan, BS_Responden FROM sosial_tahunan WHERE pencacah = ?
                            UNION ALL
                            SELECT nama_kegiatan, BS_Responden FROM sosial_triwulanan WHERE pencacah = ?
                        ) AS detail_kegiatan GROUP BY nama_kegiatan";

        $bindings = array_fill(0, 7, $namaPencacah);
        $detailKegiatan = DB::select($sqlDetail, $bindings);

        return response()->json($detailKegiatan);
    }
}