<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardProduksiController extends Controller
{
    public function index()
    {
        // 🔹 Gabungkan data dari semua tabel produksi (pakai tanggal_pengumpulan)
        $unionQuery = "
            SELECT flag_progress, pencacah, tanggal_pengumpulan FROM produksi_tahunan
            UNION ALL
            SELECT flag_progress, pencacah, tanggal_pengumpulan FROM produksi_caturwulanan
            UNION ALL
            SELECT flag_progress, pencacah, tanggal_pengumpulan FROM produksi_triwulanan
            UNION ALL
            SELECT flag_progress, pencacah, tanggal_pengumpulan FROM produksi_bulanan
        ";

        // 🔹 Ambil semua data
        $dataGabungan = DB::select($unionQuery);

        // 🔹 Hitung total statistik
        $totalSemua = count($dataGabungan);
        $totalSelesai = collect($dataGabungan)->where('flag_progress', 'Selesai')->count();
        $totalProses = collect($dataGabungan)->where('flag_progress', 'Proses')->count();
        $totalBelum = collect($dataGabungan)->where('flag_progress', 'Belum Mulai')->count();

        // 🔹 Grafik per bulan (12 bulan terakhir)
        $kegiatanPerBulan = DB::select("
            SELECT 
                DATE_FORMAT(STR_TO_DATE(tanggal_pengumpulan, '%Y-%m-%d'), '%b %Y') AS bulan,
                COUNT(*) AS total
            FROM ($unionQuery) AS u
            WHERE 
                tanggal_pengumpulan IS NOT NULL
                AND tanggal_pengumpulan != ''
                AND STR_TO_DATE(tanggal_pengumpulan, '%Y-%m-%d') BETWEEN '2020-01-01' AND CURDATE()
            GROUP BY bulan
            ORDER BY STR_TO_DATE(CONCAT('01 ', bulan), '%d %b %Y') ASC
        ");

        // 🔹 Buat array 12 bulan terakhir untuk menjaga urutan
        $bulanSekarang = Carbon::now();
        $dataBulan = [];
        for ($i = 11; $i >= 0; $i--) {
            $bulan = $bulanSekarang->copy()->subMonths($i)->format('M Y');
            $dataBulan[$bulan] = 0;
        }

        foreach ($kegiatanPerBulan as $row) {
            if (isset($dataBulan[$row->bulan])) {
                $dataBulan[$row->bulan] = $row->total;
            }
        }

        // 🔹 Top 10 pencacah paling aktif
        $kegiatanPerPencacah = DB::select("
            SELECT pencacah, COUNT(*) AS total
            FROM ($unionQuery) AS u
            WHERE pencacah IS NOT NULL AND pencacah != ''
            GROUP BY pencacah
            ORDER BY total DESC
            LIMIT 10
        ");

        return view('dashboard.produksi', [
            'dataBulan' => $dataBulan,
            'kegiatanPerPencacah' => $kegiatanPerPencacah,
            'totalSemua' => $totalSemua,
            'totalSelesai' => $totalSelesai,
            'totalProses' => $totalProses,
            'totalBelum' => $totalBelum,
        ]);
    }
}
