<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardDistribusiController extends Controller
{
    /**
     * Menampilkan halaman dashboard utama (KODE INI SUDAH ANDA MILIKI)
     */
    public function index()
    {
        // ... (Metode index() Anda tidak berubah)
        $tahunan = DB::table('distribusi_tahunan')
            ->select(
                DB::raw("COUNT(*) as total"),
                DB::raw("SUM(CASE WHEN flag_progress = 'Selesai' THEN 1 ELSE 0 END) as selesai"),
                DB::raw("SUM(CASE WHEN flag_progress = 'Proses' THEN 1 ELSE 0 END) as proses"),
                DB::raw("SUM(CASE WHEN flag_progress = 'Belum Mulai' THEN 1 ELSE 0 END) as belum_mulai")
            )
            ->first();
        $triwulanan = DB::table('distribusi_triwulanan')
            ->select(
                DB::raw("COUNT(*) as total"),
                DB::raw("SUM(CASE WHEN flag_progress = 'Selesai' THEN 1 ELSE 0 END) as selesai"),
                DB::raw("SUM(CASE WHEN flag_progress = 'Proses' THEN 1 ELSE 0 END) as proses"),
                DB::raw("SUM(CASE WHEN flag_progress = 'Belum Mulai' THEN 1 ELSE 0 END) as belum_mulai")
            )
            ->first();
        $bulanan = DB::table('distribusi_bulanan')
            ->select(
                DB::raw("COUNT(*) as total"),
                DB::raw("SUM(CASE WHEN flag_progress = 'Selesai' THEN 1 ELSE 0 END) as selesai"),
                DB::raw("SUM(CASE WHEN flag_progress = 'Proses' THEN 1 ELSE 0 END) as proses"),
                DB::raw("SUM(CASE WHEN flag_progress = 'Belum Mulai' THEN 1 ELSE 0 END) as belum_mulai")
            )
            ->first();
        $total_semua = ($tahunan->total ?? 0) + ($triwulanan->total ?? 0) + ($bulanan->total ?? 0);
        $total_selesai = ($tahunan->selesai ?? 0) + ($triwulanan->selesai ?? 0) + ($bulanan->selesai ?? 0);
        $total_proses = ($tahunan->proses ?? 0) + ($triwulanan->proses ?? 0) + ($bulanan->proses ?? 0);
        $total_belum_mulai = ($tahunan->belum_mulai ?? 0) + ($triwulanan->belum_mulai ?? 0) + ($bulanan->belum_mulai ?? 0);

        return view('dashboard.distribusi', compact(
            'tahunan',
            'triwulanan',
            'bulanan',
            'total_semua',
            'total_selesai',
            'total_proses',
            'total_belum_mulai'
        ));
    }


    // --- ⬇️ PERUBAHAN BESAR ADA DI 3 METODE DI BAWAH INI ⬇️ ---

    /**
     * Menampilkan halaman detail untuk kegiatan tahunan.
     */
    public function detailTahunan()
    {
        // Query baru: JOIN, GROUP BY, dan hitung Selesai vs Target
        $chartData = DB::table('distribusi_tahunan as dt')
            ->join('master_kegiatan as mk', 'dt.master_kegiatan_id', '=', 'mk.id_master_kegiatan')
            ->select(
                'mk.nama_kegiatan',
                DB::raw('COALESCE(mk.target, 0) as target'), // Ambil target, pastikan 0 jika null
                DB::raw("SUM(CASE WHEN dt.flag_progress = 'Selesai' THEN 1 ELSE 0 END) as realisasi_selesai")
            )
            ->groupBy('mk.id_master_kegiatan', 'mk.nama_kegiatan', 'mk.target') // Group berdasarkan master kegiatan
            ->orderBy('mk.nama_kegiatan')
            ->get();
        
        return view('dashboard.distribusi-detail', [
            'periode' => 'Tahunan',
            'chartData' => $chartData
        ]);
    }

    /**
     * Menampilkan halaman detail untuk kegiatan triwulanan.
     */
    public function detailTriwulanan()
    {
        $chartData = DB::table('distribusi_triwulanan as dt')
            ->join('master_kegiatan as mk', 'dt.master_kegiatan_id', '=', 'mk.id_master_kegiatan')
            ->select(
                'mk.nama_kegiatan',
                DB::raw('COALESCE(mk.target, 0) as target'),
                DB::raw("SUM(CASE WHEN dt.flag_progress = 'Selesai' THEN 1 ELSE 0 END) as realisasi_selesai")
            )
            ->groupBy('mk.id_master_kegiatan', 'mk.nama_kegiatan', 'mk.target')
            ->orderBy('mk.nama_kegiatan')
            ->get();
            
        return view('dashboard.distribusi-detail', [
            'periode' => 'Triwulanan',
            'chartData' => $chartData
        ]);
    }

    /**
     * Menampilkan halaman detail untuk kegiatan bulanan.
     */
    public function detailBulanan()
    {
        $chartData = DB::table('distribusi_bulanan as dt')
            ->join('master_kegiatan as mk', 'dt.master_kegiatan_id', '=', 'mk.id_master_kegiatan')
            ->select(
                'mk.nama_kegiatan',
                DB::raw('COALESCE(mk.target, 0) as target'),
                DB::raw("SUM(CASE WHEN dt.flag_progress = 'Selesai' THEN 1 ELSE 0 END) as realisasi_selesai")
            )
            ->groupBy('mk.id_master_kegiatan', 'mk.nama_kegiatan', 'mk.target')
            ->orderBy('mk.nama_kegiatan')
            ->get();
            
        return view('dashboard.distribusi-detail', [
            'periode' => 'Bulanan',
            'chartData' => $chartData
        ]);
    }
}