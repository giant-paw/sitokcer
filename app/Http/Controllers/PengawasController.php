<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PengawasController extends Controller
{
    public function index(Request $request)
    {
        $q = $request->input('q');

        $tables = [
            'distribusi_bulanan', 'produksi_bulanan', 'produksi_tahunan',
            'produksi_triwulanan', 'sosial_semesteran', 'sosial_tahunan', 'sosial_triwulanan'
        ];

        $firstTable = array_shift($tables);
        $unionQuery = DB::table($firstTable)->select('pengawas');

        foreach ($tables as $table) {
            $unionQuery->unionAll(DB::table($table)->select('pengawas'));
        }

        $query = DB::query()->fromSub($unionQuery, 'data_gabungan')
            ->select(
                'pengawas as nama_pengawas',
                DB::raw('COUNT(*) as total_responden')
            )
            ->whereNotNull('pengawas')
            ->where('pengawas', '!=', '');

        if ($q) {
            $query->where('pengawas', 'like', "{$q}%");
        }
        
        $rekapPengawas = $query->groupBy('pengawas')
            ->orderBy('nama_pengawas', 'asc')
            ->paginate(10)
            ->withQueryString();

        return view('Rekapitulasi.pengawas.index', [
            'rekapPengawas' => $rekapPengawas,
            'q' => $q
        ]);
    }

    /**
     * INI FUNGSI YANG DIPERBAIKI
     * Mengambil detail pencacah yang diawasi oleh pengawas tertentu.
     */
    public function getDetailPencacah($nama)
    {
        $namaPengawas = urldecode($nama);

        $tables = [
            'distribusi_bulanan', 'produksi_bulanan', 'produksi_tahunan',
            'produksi_triwulanan', 'sosial_semesteran', 'sosial_tahunan', 'sosial_triwulanan'
        ];

        // Query ini perlu mengambil 'pencacah' dan 'pengawas' untuk difilter
        $firstTable = array_shift($tables);
        $unionQuery = DB::table($firstTable)->select('pencacah', 'pengawas');

        foreach ($tables as $table) {
            $unionQuery->unionAll(DB::table($table)->select('pencacah', 'pengawas'));
        }

        // Query untuk mengambil detail, mengelompokkan per pencacah
        $detailPencacah = DB::query()->fromSub($unionQuery, 'data_gabungan')
            ->select('pencacah', DB::raw('COUNT(*) as jumlah_responden'))
            ->where('pengawas', $namaPengawas)
            ->whereNotNull('pencacah')
            ->where('pencacah', '!=', '')
            ->groupBy('pencacah')
            ->orderBy('pencacah', 'asc')
            ->get();

        // Mengembalikan data dalam format JSON yang akan dibaca oleh JavaScript
        return response()->json($detailPencacah);
    }
}