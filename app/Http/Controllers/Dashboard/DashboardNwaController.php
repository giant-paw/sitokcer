<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardNwaController extends Controller
{
    public function index()
    {
        // 🔹 Cari nama kolom tanggal yang tersedia di masing-masing tabel
        $columnsTahunan = DB::select("SHOW COLUMNS FROM nwa_tahunan");
        $columnsTriwulan = DB::select("SHOW COLUMNS FROM nwa_triwulanan");

        $tanggalTahunan = $this->cariKolomTanggal($columnsTahunan);
        $tanggalTriwulan = $this->cariKolomTanggal($columnsTriwulan);

        // 🔹 Gabungkan data (pakai kolom tanggal yang ditemukan)
        $unionQuery = "
            SELECT flag_progress, pencacah, $tanggalTahunan AS tanggal FROM nwa_tahunan
            UNION ALL
            SELECT flag_progress, pencacah, $tanggalTriwulan AS tanggal FROM nwa_triwulanan
        ";

        $dataGabungan = DB::select($unionQuery);

        // 🔹 Hitung total ringkasan
        $totalSemua = count($dataGabungan);
        $totalSelesai = collect($dataGabungan)->where('flag_progress', 'Selesai')->count();
        $totalProses = collect($dataGabungan)->where('flag_progress', 'Proses')->count();
        $totalBelum = collect($dataGabungan)->where('flag_progress', 'Belum Mulai')->count();

        // 🔹 Grafik per bulan
        $kegiatanPerBulan = DB::select("
            SELECT 
                DATE_FORMAT(STR_TO_DATE(tanggal, '%Y-%m-%d'), '%b %Y') AS bulan,
                COUNT(*) AS total
            FROM ($unionQuery) AS u
            WHERE 
                tanggal IS NOT NULL 
                AND tanggal != '' 
                AND STR_TO_DATE(tanggal, '%Y-%m-%d') BETWEEN '2020-01-01' AND CURDATE()
            GROUP BY bulan
            ORDER BY STR_TO_DATE(CONCAT('01 ', bulan), '%d %b %Y') ASC
        ");

        // 🔹 Format 12 bulan terakhir
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

        // 🔹 Top 10 pencacah
        $kegiatanPerPencacah = DB::select("
            SELECT pencacah, COUNT(*) AS total
            FROM ($unionQuery) AS u
            WHERE pencacah IS NOT NULL AND pencacah != ''
            GROUP BY pencacah
            ORDER BY total DESC
            LIMIT 10
        ");

        return view('dashboard.nwa', [
            'dataBulan' => $dataBulan,
            'kegiatanPerPencacah' => $kegiatanPerPencacah,
            'totalSemua' => $totalSemua,
            'totalSelesai' => $totalSelesai,
            'totalProses' => $totalProses,
            'totalBelum' => $totalBelum,
        ]);
    }

    private function cariKolomTanggal($columns)
    {
        $namaKolom = collect($columns)->pluck('Field')->toArray();
        foreach ($namaKolom as $col) {
            if (stripos($col, 'tgl') !== false || stripos($col, 'tanggal') !== false) {
                return $col;
            }
        }
        // fallback kalau tidak ditemukan
        return 'created_at';
    }
}
