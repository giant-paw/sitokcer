<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardProduksiController extends Controller
{
    /**
     * Mendapatkan tahun yang tersedia dari semua tabel produksi berdasarkan created_at.
     */
    private function getAvailableYears()
    {
        // [PERUBAHAN] Mengambil data dari 4 tabel produksi
        $yearsT = DB::table('produksi_tahunan')->select(DB::raw('YEAR(created_at) as tahun'))->whereNotNull('created_at')->distinct();
        $yearsC = DB::table('produksi_caturwulanan')->select(DB::raw('YEAR(created_at) as tahun'))->whereNotNull('created_at')->distinct(); // <-- DIUBAH DI SINI
        $yearsTR = DB::table('produksi_triwulanan')->select(DB::raw('YEAR(created_at) as tahun'))->whereNotNull('created_at')->distinct();
        $yearsB = DB::table('produksi_bulanan')->select(DB::raw('YEAR(created_at) as tahun'))->whereNotNull('created_at')->distinct();

        $years = $yearsT->union($yearsC)->union($yearsTR)->union($yearsB) // Menambah union
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
        
        // --- [PERUBAHAN] Mengambil data dari 4 tabel produksi ---
        $tahunan = DB::table('produksi_tahunan')
            ->select(
                DB::raw("COUNT(*) as total"),
                DB::raw("SUM(CASE WHEN flag_progress = 'Selesai' THEN 1 ELSE 0 END) as selesai"),
                DB::raw("SUM(CASE WHEN flag_progress != 'Selesai' THEN 1 ELSE 0 END) as belum_selesai")
            )
            ->whereYear('created_at', $selectedTahun)
            ->first();
            
        $caturwulan = DB::table('produksi_caturwulanan') // <-- DIUBAH DI SINI
            ->select(
                DB::raw("COUNT(*) as total"),
                DB::raw("SUM(CASE WHEN flag_progress = 'Selesai' THEN 1 ELSE 0 END) as selesai"),
                DB::raw("SUM(CASE WHEN flag_progress != 'Selesai' THEN 1 ELSE 0 END) as belum_selesai")
            )
            ->whereYear('created_at', $selectedTahun)
            ->first();

        $triwulanan = DB::table('produksi_triwulanan')
            ->select(
                DB::raw("COUNT(*) as total"),
                DB::raw("SUM(CASE WHEN flag_progress = 'Selesai' THEN 1 ELSE 0 END) as selesai"),
                DB::raw("SUM(CASE WHEN flag_progress != 'Selesai' THEN 1 ELSE 0 END) as belum_selesai")
            )
            ->whereYear('created_at', $selectedTahun)
            ->first();
            
        $bulanan = DB::table('produksi_bulanan') // [BARU]
            ->select(
                DB::raw("COUNT(*) as total"),
                DB::raw("SUM(CASE WHEN flag_progress = 'Selesai' THEN 1 ELSE 0 END) as selesai"),
                DB::raw("SUM(CASE WHEN flag_progress != 'Selesai' THEN 1 ELSE 0 END) as belum_selesai")
            )
            ->whereYear('created_at', $selectedTahun)
            ->first();
            
        // --- (Data Ringkasan) ---
        // [PERUBAHAN] Menjumlahkan 4 periode
        $total_semua = ($tahunan->total ?? 0) + ($caturwulan->total ?? 0) + ($triwulanan->total ?? 0) + ($bulanan->total ?? 0);
        $total_selesai = ($tahunan->selesai ?? 0) + ($caturwulan->selesai ?? 0) + ($triwulanan->selesai ?? 0) + ($bulanan->selesai ?? 0);
        $total_belum_selesai = ($tahunan->belum_selesai ?? 0) + ($caturwulan->belum_selesai ?? 0) + ($triwulanan->belum_selesai ?? 0) + ($bulanan->belum_selesai ?? 0);

        // [PERUBAHAN] Mengirim 4 periode ke view 'dashboard.produksi'
        return view('dashboard.produksi', compact(
            'tahunan', 'caturwulan', 'triwulanan', 'bulanan', // 4 periode
            'total_semua', 'total_selesai', 'total_belum_selesai', 
            'selectedTahun', 'availableTahun'
        ));
    }

    /**
     * Halaman detail kegiatan tahunan.
     */
    public function detailTahunan($tahun) 
    {
        $chartData = DB::table('produksi_tahunan as dt')
            ->join('master_kegiatan as mk', 'dt.master_kegiatan_id', '=', 'mk.id_master_kegiatan')
            ->select('mk.nama_kegiatan', DB::raw('COALESCE(mk.target, 0) as target'), DB::raw("SUM(CASE WHEN dt.flag_progress = 'Selesai' THEN 1 ELSE 0 END) as realisasi_selesai"))
            ->whereYear('dt.created_at', $tahun)
            ->groupBy('mk.id_master_kegiatan', 'mk.nama_kegiatan', 'mk.target')
            ->orderBy('mk.nama_kegiatan')
            ->get();
        
        return view('dashboard.produksi-detail', [
            'periode' => 'Tahunan',
            'chartData' => $chartData,
            'selectedTahun' => $tahun 
        ]);
    }

    /**
     * [BARU] Halaman detail kegiatan caturwulanan.
     */
    public function detailCaturwulan($tahun) 
    {
        $chartData = DB::table('produksi_caturwulanan as dt')
            ->join('master_kegiatan as mk', 'dt.master_kegiatan_id', '=', 'mk.id_master_kegiatan')
            ->select('mk.nama_kegiatan', DB::raw('COALESCE(mk.target, 0) as target'), DB::raw("SUM(CASE WHEN dt.flag_progress = 'Selesai' THEN 1 ELSE 0 END) as realisasi_selesai"))
            ->whereYear('dt.created_at', $tahun)
            ->groupBy('mk.id_master_kegiatan', 'mk.nama_kegiatan', 'mk.target')
            ->orderBy('mk.nama_kegiatan')
            ->get();
        
        return view('dashboard.produksi-detail', [
            'periode' => 'Caturwulan',
            'chartData' => $chartData,
            'selectedTahun' => $tahun 
        ]);
    }

    /**
     * Halaman detail kegiatan triwulanan.
     */
    public function detailTriwulanan($tahun)
    {
        $chartData = DB::table('produksi_triwulanan as dt')
            ->join('master_kegiatan as mk', 'dt.master_kegiatan_id', '=', 'mk.id_master_kegiatan')
            ->select('mk.nama_kegiatan', DB::raw('COALESCE(mk.target, 0) as target'), DB::raw("SUM(CASE WHEN dt.flag_progress = 'Selesai' THEN 1 ELSE 0 END) as realisasi_selesai"))
            ->whereYear('dt.created_at', $tahun) 
            ->groupBy('mk.id_master_kegiatan', 'mk.nama_kegiatan', 'mk.target')
            ->orderBy('mk.nama_kegiatan')
            ->get();
            
        return view('dashboard.produksi-detail', [
            'periode' => 'Triwulanan',
            'chartData' => $chartData,
            'selectedTahun' => $tahun
        ]);
    }

    /**
     * [BARU] Halaman detail kegiatan bulanan.
     */
    public function detailBulanan($tahun)
    {
        $chartData = DB::table('produksi_bulanan as dt') 
            ->join('master_kegiatan as mk', 'dt.master_kegiatan_id', '=', 'mk.id_master_kegiatan')
            ->select('mk.nama_kegiatan', DB::raw('COALESCE(mk.target, 0) as target'), DB::raw("SUM(CASE WHEN dt.flag_progress = 'Selesai' THEN 1 ELSE 0 END) as realisasi_selesai"))
            ->whereYear('dt.created_at', $tahun)
            ->groupBy('mk.id_master_kegiatan', 'mk.nama_kegiatan', 'mk.target')
            ->orderBy('mk.nama_kegiatan')
            ->get();
            
        return view('dashboard.produksi-detail', [
            'periode' => 'Bulanan',
            'chartData' => $chartData,
            'selectedTahun' => $tahun
        ]);
    }
}