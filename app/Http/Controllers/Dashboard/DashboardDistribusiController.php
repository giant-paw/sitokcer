<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardDistribusiController extends Controller
{
    /**
     * Mendapatkan tahun yang tersedia dari semua tabel distribusi berdasarkan created_at.
     */
    private function getAvailableYears()
    {
        $yearsT = DB::table('distribusi_tahunan')->select(DB::raw('YEAR(created_at) as tahun'))->whereNotNull('created_at')->distinct();
        $yearsTR = DB::table('distribusi_triwulanan')->select(DB::raw('YEAR(created_at) as tahun'))->whereNotNull('created_at')->distinct();
        $yearsB = DB::table('distribusi_bulanan')->select(DB::raw('YEAR(created_at) as tahun'))->whereNotNull('created_at')->distinct();

        $years = $yearsT->union($yearsTR)->union($yearsB)
                      ->orderBy('tahun', 'desc')
                      ->pluck('tahun')
                      ->toArray();
        
        // Pastikan tahun ini ada jika belum ada data
        $currentYear = date('Y');
        if (empty($years) || !in_array($currentYear, $years)) {
            array_unshift($years, $currentYear);
        }
        return $years;
    }

    /**
     * Menampilkan halaman dashboard utama (index)
     */
    public function index(Request $request, $tahun = null)
    {
        // Default ke tahun saat ini jika $tahun tidak ada
        $selectedTahun = $tahun ?? $request->input('tahun', date('Y'));
        
        $availableTahun = $this->getAvailableYears();
        
        // --- [PERUBAHAN LOGIKA 2 STATUS] ---
        $tahunan = DB::table('distribusi_tahunan')
            ->select(
                DB::raw("COUNT(*) as total"),
                DB::raw("SUM(CASE WHEN flag_progress = 'Selesai' THEN 1 ELSE 0 END) as selesai"),
                DB::raw("SUM(CASE WHEN flag_progress != 'Selesai' THEN 1 ELSE 0 END) as belum_selesai")
            )
            ->whereYear('created_at', $selectedTahun)
            ->first();
            
        $triwulanan = DB::table('distribusi_triwulanan')
            ->select(
                DB::raw("COUNT(*) as total"),
                DB::raw("SUM(CASE WHEN flag_progress = 'Selesai' THEN 1 ELSE 0 END) as selesai"),
                DB::raw("SUM(CASE WHEN flag_progress != 'Selesai' THEN 1 ELSE 0 END) as belum_selesai")
            )
            ->whereYear('created_at', $selectedTahun)
            ->first();
            
        $bulanan = DB::table('distribusi_bulanan')
            ->select(
                DB::raw("COUNT(*) as total"),
                DB::raw("SUM(CASE WHEN flag_progress = 'Selesai' THEN 1 ELSE 0 END) as selesai"),
                DB::raw("SUM(CASE WHEN flag_progress != 'Selesai' THEN 1 ELSE 0 END) as belum_selesai")
            )
            ->whereYear('created_at', $selectedTahun)
            ->first();
            
        // --- (Data Ringkasan) ---
        $total_semua = ($tahunan->total ?? 0) + ($triwulanan->total ?? 0) + ($bulanan->total ?? 0);
        $total_selesai = ($tahunan->selesai ?? 0) + ($triwulanan->selesai ?? 0) + ($bulanan->selesai ?? 0);
        $total_belum_selesai = ($tahunan->belum_selesai ?? 0) + ($triwulanan->belum_selesai ?? 0) + ($bulanan->belum_selesai ?? 0);

        return view('dashboard.distribusi', compact(
            'tahunan', 'triwulanan', 'bulanan',
            'total_semua', 'total_selesai', 'total_belum_selesai', // Hanya kirim 2 status
            'selectedTahun', 'availableTahun'
        ));
    }

    /**
     * Menampilkan halaman detail untuk kegiatan tahunan.
     */
    public function detailTahunan($tahun) 
    {
        $chartData = DB::table('distribusi_tahunan as dt')
            ->join('master_kegiatan as mk', 'dt.master_kegiatan_id', '=', 'mk.id_master_kegiatan')
            ->select(
                'mk.nama_kegiatan',
                DB::raw('COALESCE(mk.target, 0) as target'),
                DB::raw("SUM(CASE WHEN dt.flag_progress = 'Selesai' THEN 1 ELSE 0 END) as realisasi_selesai")
            )
            ->whereYear('dt.created_at', $tahun) // Filter berdasarkan created_at
            ->groupBy('mk.id_master_kegiatan', 'mk.nama_kegiatan', 'mk.target')
            ->orderBy('mk.nama_kegiatan')
            ->get();
        
        return view('dashboard.distribusi-detail', [
            'periode' => 'Tahunan',
            'chartData' => $chartData,
            'selectedTahun' => $tahun 
        ]);
    }

    /**
     * Menampilkan halaman detail untuk kegiatan triwulanan.
     */
    public function detailTriwulanan($tahun)
    {
        $chartData = DB::table('distribusi_triwulanan as dt')
            ->join('master_kegiatan as mk', 'dt.master_kegiatan_id', '=', 'mk.id_master_kegiatan')
            ->select(
                'mk.nama_kegiatan',
                DB::raw('COALESCE(mk.target, 0) as target'),
                DB::raw("SUM(CASE WHEN dt.flag_progress = 'Selesai' THEN 1 ELSE 0 END) as realisasi_selesai")
            )
            ->whereYear('dt.created_at', $tahun) 
            ->groupBy('mk.id_master_kegiatan', 'mk.nama_kegiatan', 'mk.target')
            ->orderBy('mk.nama_kegiatan')
            ->get();
            
        return view('dashboard.distribusi-detail', [
            'periode' => 'Triwulanan',
            'chartData' => $chartData,
            'selectedTahun' => $tahun
        ]);
    }

    /**
     * Menampilkan halaman detail untuk kegiatan bulanan.
     */
    public function detailBulanan($tahun)
    {
        $chartData = DB::table('distribusi_bulanan as dt')
            ->join('master_kegiatan as mk', 'dt.master_kegiatan_id', '=', 'mk.id_master_kegiatan')
            ->select(
                'mk.nama_kegiatan',
                DB::raw('COALESCE(mk.target, 0) as target'),
                DB::raw("SUM(CASE WHEN dt.flag_progress = 'Selesai' THEN 1 ELSE 0 END) as realisasi_selesai")
            )
            ->whereYear('dt.created_at', $tahun)
            ->groupBy('mk.id_master_kegiatan', 'mk.nama_kegiatan', 'mk.target')
            ->orderBy('mk.nama_kegiatan')
            ->get();
            
        return view('dashboard.distribusi-detail', [
            'periode' => 'Bulanan',
            'chartData' => $chartData,
            'selectedTahun' => $tahun
        ]);
    }
}