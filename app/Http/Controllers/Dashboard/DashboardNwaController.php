<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardNwaController extends Controller
{
    /**
     * Mendapatkan tahun yang tersedia dari semua tabel NWA.
     */
    private function getAvailableYears()
    {
        $yearsT = DB::table('nwa_tahunan')->select(DB::raw('YEAR(created_at) as tahun'))->whereNotNull('created_at')->distinct();
        $yearsTR = DB::table('nwa_triwulanan')->select(DB::raw('YEAR(created_at) as tahun'))->whereNotNull('created_at')->distinct();

        $years = $yearsT->union($yearsTR)
                        ->orderBy('tahun', 'desc')
                        ->pluck('tahun')
                        ->toArray();
        
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
        $selectedTahun = $tahun ?? $request->input('tahun', date('Y'));
        $availableTahun = $this->getAvailableYears();
        
        // --- Mengambil data dari 2 tabel NWA ---
        $tahunan = DB::table('nwa_tahunan')
            ->select(
                DB::raw("COUNT(*) as total"),
                DB::raw("SUM(CASE WHEN flag_progress = 'Selesai' THEN 1 ELSE 0 END) as selesai"),
                DB::raw("SUM(CASE WHEN flag_progress != 'Selesai' THEN 1 ELSE 0 END) as belum_selesai")
            )
            ->whereYear('created_at', $selectedTahun)
            ->first();

        $triwulanan = DB::table('nwa_triwulanan')
            ->select(
                DB::raw("COUNT(*) as total"),
                DB::raw("SUM(CASE WHEN flag_progress = 'Selesai' THEN 1 ELSE 0 END) as selesai"),
                DB::raw("SUM(CASE WHEN flag_progress != 'Selesai' THEN 1 ELSE 0 END) as belum_selesai")
            )
            ->whereYear('created_at', $selectedTahun)
            ->first();
            
        // --- (Data Ringkasan) ---
        $total_semua = ($tahunan->total ?? 0) + ($triwulanan->total ?? 0);
        $total_selesai = ($tahunan->selesai ?? 0) + ($triwulanan->selesai ?? 0);
        $total_belum_selesai = ($tahunan->belum_selesai ?? 0) + ($triwulanan->belum_selesai ?? 0);

        // Mengirim 2 periode ke view 'dashboard.nwa'
        return view('dashboard.nwa', compact(
            'tahunan', 'triwulanan', 
            'total_semua', 'total_selesai', 'total_belum_selesai', 
            'selectedTahun', 'availableTahun'
        ));
    }

    /**
     * Halaman detail kegiatan tahunan.
     */
    public function detailTahunan($tahun) 
    {
        $chartData = DB::table('nwa_tahunan as dt')
            ->join('master_kegiatan as mk', 'dt.master_kegiatan_id', '=', 'mk.id_master_kegiatan')
            ->select('mk.nama_kegiatan', DB::raw('COALESCE(mk.target, 0) as target'), DB::raw("SUM(CASE WHEN dt.flag_progress = 'Selesai' THEN 1 ELSE 0 END) as realisasi_selesai"))
            ->whereYear('dt.created_at', $tahun)
            ->groupBy('mk.id_master_kegiatan', 'mk.nama_kegiatan', 'mk.target')
            ->orderBy('mk.nama_kegiatan')
            ->get();
        
        return view('dashboard.nwa-detail', [
            'periode' => 'Tahunan',
            'chartData' => $chartData,
            'selectedTahun' => $tahun 
        ]);
    }

    /**
     * Halaman detail kegiatan triwulanan.
     */
    public function detailTriwulanan($tahun)
    {
        $chartData = DB::table('nwa_triwulanan as dt')
            ->join('master_kegiatan as mk', 'dt.master_kegiatan_id', '=', 'mk.id_master_kegiatan')
            ->select('mk.nama_kegiatan', DB::raw('COALESCE(mk.target, 0) as target'), DB::raw("SUM(CASE WHEN dt.flag_progress = 'Selesai' THEN 1 ELSE 0 END) as realisasi_selesai"))
            ->whereYear('dt.created_at', $tahun) 
            ->groupBy('mk.id_master_kegiatan', 'mk.nama_kegiatan', 'mk.target')
            ->orderBy('mk.nama_kegiatan')
            ->get();
            
        return view('dashboard.nwa-detail', [
            'periode' => 'Triwulanan',
            'chartData' => $chartData,
            'selectedTahun' => $tahun
        ]);
    }
}