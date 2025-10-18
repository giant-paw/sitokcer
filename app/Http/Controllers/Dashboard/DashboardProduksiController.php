<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardProduksiController extends Controller
{
    public function index()
    {
        // Fungsi untuk mengambil statistik dari tabel
        $getStats = function ($tableName) {
            return DB::table($tableName)
                ->select(
                    DB::raw("COUNT(*) as total"),
                    DB::raw("SUM(CASE WHEN flag_progress = 'Selesai' THEN 1 ELSE 0 END) as selesai"),
                    DB::raw("SUM(CASE WHEN flag_progress = 'Proses' THEN 1 ELSE 0 END) as proses"),
                    DB::raw("SUM(CASE WHEN flag_progress = 'Belum Mulai' THEN 1 ELSE 0 END) as belum_mulai")
                )
                ->first();
        };

        // Mengambil data dari masing-masing tabel
        $tahunan = $getStats('produksi_tahunan');
        $caturwulanan = $getStats('produksi_caturwulanan');
        $triwulanan = $getStats('produksi_triwulanan');
        $bulanan = $getStats('produksi_bulanan');

        // Menghitung total keseluruhan
        $total_semua = ($tahunan->total ?? 0) + ($caturwulanan->total ?? 0) + ($triwulanan->total ?? 0) + ($bulanan->total ?? 0);
        $total_selesai = ($tahunan->selesai ?? 0) + ($caturwulanan->selesai ?? 0) + ($triwulanan->selesai ?? 0) + ($bulanan->selesai ?? 0);
        $total_proses = ($tahunan->proses ?? 0) + ($caturwulanan->proses ?? 0) + ($triwulanan->proses ?? 0) + ($bulanan->proses ?? 0);
        $total_belum_mulai = ($tahunan->belum_mulai ?? 0) + ($caturwulanan->belum_mulai ?? 0) + ($triwulanan->belum_mulai ?? 0) + ($bulanan->belum_mulai ?? 0);

        return view('dashboard.produksi', compact(
            'tahunan',
            'caturwulanan',
            'triwulanan',
            'bulanan',
            'total_semua',
            'total_selesai',
            'total_proses',
            'total_belum_mulai'
        ));
    }
}