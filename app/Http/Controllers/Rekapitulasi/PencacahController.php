<?php

namespace App\Http\Controllers\Rekapitulasi;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PencacahController extends Controller
{
    public function index(Request $request)
    {
        $q = $request->input('q');

        $tables = [
            'distribusi_bulanan', 'produksi_bulanan', 'produksi_tahunan',
            'produksi_triwulanan', 'sosial_semesteran', 'sosial_tahunan', 'sosial_triwulanan'
        ];

        $firstTable = array_shift($tables);
        $unionQuery = DB::table($firstTable)->select('pencacah');

        foreach ($tables as $table) {
            $unionQuery->unionAll(DB::table($table)->select('pencacah'));
        }

        $query = DB::query()->fromSub($unionQuery, 'data_gabungan')
            ->select(
                'pencacah as nama_pencacah',
                DB::raw('COUNT(*) as total_responden')
            )
            ->whereNotNull('pencacah')
            ->where('pencacah', '!=', '');

        if ($q) {
            $query->where('pencacah', 'like', "{$q}%");
        }
        
        $rekapPencacah = $query->groupBy('pencacah')
            ->orderBy('nama_pencacah', 'asc')
            ->paginate(10)
            ->withQueryString();

        return view('Rekapitulasi.pencacah.index', [
            'rekapPencacah' => $rekapPencacah,
            'q' => $q
        ]);
    }

    public function printAll(Request $request)
    {
        $q = $request->input('q');
        $tables = ['distribusi_bulanan', 'produksi_bulanan', 'produksi_tahunan', 'produksi_triwulanan', 'sosial_semesteran', 'sosial_tahunan', 'sosial_triwulanan'];
        
        $firstTable = array_shift($tables);
        $unionQuery = DB::table($firstTable)->select('pencacah');
        foreach ($tables as $table) {
            $unionQuery->unionAll(DB::table($table)->select('pencacah'));
        }

        $query = DB::query()->fromSub($unionQuery, 'data_gabungan')
            ->select('pencacah as nama_pencacah', DB::raw('COUNT(*) as total_responden'))
            ->whereNotNull('pencacah')->where('pencacah', '!=', '');
        
        if ($q) {
            $query->where('pencacah', 'like', "{$q}%");
        }
        
        $rekapPencacah = $query->groupBy('pencacah')->orderBy('nama_pencacah', 'asc')->get();

        // [ PERBAIKAN DI SINI ]
        // Simpan kembali hasil dari .map() ke variabel $rekapPencacah
        $rekapPencacah = $rekapPencacah->map(function ($pencacah) {
            // Memanggil method getDetailKegiatan dan mengambil data aslinya (array)
            $kegiatanData = $this->getDetailKegiatan($pencacah->nama_pencacah)->original;
            
            // Menambahkan properti 'kegiatan' ke objek $pencacah
            $pencacah->kegiatan = $kegiatanData;
            
            return $pencacah;
        });

        return view('Rekapitulasi.pencacah.print', [
            'rekapPencacah' => $rekapPencacah,
            'q' => $q
        ]);
    }

    public function printSelectedData(Request $request)
    {
        $pencacahNames = $request->input('pencacah', []);
        if (empty($pencacahNames)) {
            return response()->json([]);
        }

        $data = collect($pencacahNames)->map(function ($nama) {
            $detailKegiatan = $this->getDetailKegiatan($nama)->original;
            $totalResponden = collect($detailKegiatan)->sum('jumlah_responden');

            return [
                'nama_pencacah' => $nama,
                'total_responden' => $totalResponden,
                'kegiatan' => $detailKegiatan,
            ];
        });

        return response()->json($data);
    }

    public function getDetailKegiatan($nama)
    {
        $namaPencacah = urldecode($nama);
        
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