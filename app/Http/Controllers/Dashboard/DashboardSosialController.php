<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardSosialController extends Controller
{
    /**
     * Mendapatkan tahun yang tersedia dari semua tabel sosial berdasarkan created_at.
     */
    private function getAvailableYears()
    {
        // [PERUBAHAN] Menggunakan tabel sosial
        $yearsT = DB::table('sosial_tahunan')->select(DB::raw('YEAR(created_at) as tahun'))->whereNotNull('created_at')->distinct();
        $yearsTR = DB::table('sosial_triwulanan')->select(DB::raw('YEAR(created_at) as tahun'))->whereNotNull('created_at')->distinct();
        $yearsS = DB::table('sosial_semesteran')->select(DB::raw('YEAR(created_at) as tahun'))->whereNotNull('created_at')->distinct(); // Diubah ke semesteran

        $years = $yearsT->union($yearsTR)->union($yearsS) // Diubah ke semesteran
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
        
        // --- [PERUBAHAN] Menggunakan tabel sosial ---
        $tahunan = DB::table('sosial_tahunan')
            ->select(
                DB::raw("COUNT(*) as total"),
                DB::raw("SUM(CASE WHEN flag_progress = 'Selesai' THEN 1 ELSE 0 END) as selesai"),
                DB::raw("SUM(CASE WHEN flag_progress != 'Selesai' THEN 1 ELSE 0 END) as belum_selesai")
            )
            ->whereYear('created_at', $selectedTahun)
            ->first();
            
        $triwulanan = DB::table('sosial_triwulanan')
            ->select(
                DB::raw("COUNT(*) as total"),
                DB::raw("SUM(CASE WHEN flag_progress = 'Selesai' THEN 1 ELSE 0 END) as selesai"),
                DB::raw("SUM(CASE WHEN flag_progress != 'Selesai' THEN 1 ELSE 0 END) as belum_selesai")
            )
            ->whereYear('created_at', $selectedTahun)
            ->first();
            
        // [PERUBAHAN] Diubah ke semesteran
        $semesteran = DB::table('sosial_semesteran') 
            ->select(
                DB::raw("COUNT(*) as total"),
                DB::raw("SUM(CASE WHEN flag_progress = 'Selesai' THEN 1 ELSE 0 END) as selesai"),
                DB::raw("SUM(CASE WHEN flag_progress != 'Selesai' THEN 1 ELSE 0 END) as belum_selesai")
            )
            ->whereYear('created_at', $selectedTahun)
            ->first();
            
        // --- (Data Ringkasan) ---
        // [PERUBAHAN] Menggunakan $semesteran
        $total_semua = ($tahunan->total ?? 0) + ($triwulanan->total ?? 0) + ($semesteran->total ?? 0);
        $total_selesai = ($tahunan->selesai ?? 0) + ($triwulanan->selesai ?? 0) + ($semesteran->selesai ?? 0);
        $total_belum_selesai = ($tahunan->belum_selesai ?? 0) + ($triwulanan->belum_selesai ?? 0) + ($semesteran->belum_selesai ?? 0);

        // [PERUBAHAN] Mengirim $semesteran dan view 'dashboard.sosial'
        return view('dashboard.sosial', compact(
            'tahunan', 'triwulanan', 'semesteran', // Diubah ke semesteran
            'total_semua', 'total_selesai', 'total_belum_selesai', 
            'selectedTahun', 'availableTahun'
        ));
    }

    /**
     * Menampilkan halaman detail untuk kegiatan tahunan.
     */
    public function detailTahunan($tahun) 
    {
        // [PERUBAHAN] Menggunakan tabel sosial_tahunan
        $chartData = DB::table('sosial_tahunan as dt')
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
        
        // [PERUBAHAN] Menggunakan view 'dashboard.sosial-detail'
        return view('dashboard.sosial-detail', [
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
        // [PERUBAHAN] Menggunakan tabel sosial_triwulanan
        $chartData = DB::table('sosial_triwulanan as dt')
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
            
        // [PERUBAHAN] Menggunakan view 'dashboard.sosial-detail'
        return view('dashboard.sosial-detail', [
            'periode' => 'Triwulanan',
            'chartData' => $chartData,
            'selectedTahun' => $tahun
        ]);
    }

    /**
     * Menampilkan halaman detail untuk kegiatan semesteran.
     */
    public function detailSemesteran($tahun) // [PERUBAHAN] Fungsi diubah
    {
        // [PERUBAHAN] Menggunakan tabel sosial_semesteran
        $chartData = DB::table('sosial_semesteran as dt') 
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
            
        // [PERUBAHAN] Menggunakan view 'dashboard.sosial-detail'
        return view('dashboard.sosial-detail', [
            'periode' => 'Semesteran', // Diubah
            'chartData' => $chartData,
            'selectedTahun' => $tahun
        ]);
    }
}