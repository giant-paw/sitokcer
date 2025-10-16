<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PengawasController extends Controller
{
    public function index(Request $request)
    {
        // ... (Method index tidak berubah)
        $q = $request->input('q');
        $tables = ['distribusi_bulanan', 'produksi_bulanan', 'produksi_tahunan', 'produksi_triwulanan', 'sosial_semesteran', 'sosial_tahunan', 'sosial_triwulanan'];
        $firstTable = array_shift($tables);
        $unionQuery = DB::table($firstTable)->select('pengawas');
        foreach ($tables as $table) {
            $unionQuery->unionAll(DB::table($table)->select('pengawas'));
        }
        $query = DB::query()->fromSub($unionQuery, 'data_gabungan')
            ->select('pengawas as nama_pengawas', DB::raw('COUNT(*) as total_responden'))
            ->whereNotNull('pengawas')->where('pengawas', '!=', '');
        if ($q) {
            $query->where('pengawas', 'like', "{$q}%");
        }
        $rekapPengawas = $query->groupBy('pengawas')->orderBy('nama_pengawas', 'asc')->paginate(10)->withQueryString();

        return view('Rekapitulasi.pengawas.index', ['rekapPengawas' => $rekapPengawas, 'q' => $q]);
    }

    // [ BARU ] Method untuk halaman "Cetak Semua"
    public function printAll(Request $request)
    {
        $q = $request->input('q');
        $tables = ['distribusi_bulanan', 'produksi_bulanan', 'produksi_tahunan', 'produksi_triwulanan', 'sosial_semesteran', 'sosial_tahunan', 'sosial_triwulanan'];
        $firstTable = array_shift($tables);
        $unionQuery = DB::table($firstTable)->select('pengawas');
        foreach ($tables as $table) {
            $unionQuery->unionAll(DB::table($table)->select('pengawas'));
        }
        $query = DB::query()->fromSub($unionQuery, 'data_gabungan')
            ->select('pengawas as nama_pengawas', DB::raw('COUNT(*) as total_responden'))
            ->whereNotNull('pengawas')->where('pengawas', '!=', '');
        if ($q) {
            $query->where('pengawas', 'like', "{$q}%");
        }
        $rekapPengawas = $query->groupBy('pengawas')->orderBy('nama_pengawas', 'asc')->get();

        // Ambil detail pencacah untuk setiap pengawas
        $rekapPengawas = $rekapPengawas->map(function ($pengawas) {
            $pengawas->pencacah_list = $this->getDetailPencacah($pengawas->nama_pengawas)->original;
            return $pengawas;
        });

        return view('Rekapitulasi.pengawas.print', [
            'rekapPengawas' => $rekapPengawas,
            'q' => $q
        ]);
    }

    // [ BARU ] Method untuk melayani AJAX request untuk data cetak
    public function printSelectedData(Request $request)
    {
        $pengawasNames = $request->input('pengawas', []);
        if (empty($pengawasNames)) {
            return response()->json([]);
        }

        $data = collect($pengawasNames)->map(function ($nama) {
            $detailPencacah = $this->getDetailPencacah($nama)->original;
            $totalResponden = collect($detailPencacah)->sum('jumlah_responden');

            return [
                'nama_pengawas' => $nama,
                'total_responden' => $totalResponden,
                'pencacah_list' => $detailPencacah,
            ];
        });

        return response()->json($data);
    }
    
    public function getDetailPencacah($nama)
    {
        // ... (Method ini tidak berubah, sudah benar)
        $namaPengawas = urldecode($nama);
        $tables = ['distribusi_bulanan', 'produksi_bulanan', 'produksi_tahunan', 'produksi_triwulanan', 'sosial_semesteran', 'sosial_tahunan', 'sosial_triwulanan'];
        $firstTable = array_shift($tables);
        $unionQuery = DB::table($firstTable)->select('pencacah', 'pengawas');
        foreach ($tables as $table) {
            $unionQuery->unionAll(DB::table($table)->select('pencacah', 'pengawas'));
        }
        $detailPencacah = DB::query()->fromSub($unionQuery, 'data_gabungan')
            ->select('pencacah', DB::raw('COUNT(*) as jumlah_responden'))
            ->where('pengawas', $namaPengawas)
            ->whereNotNull('pencacah')->where('pencacah', '!=', '')
            ->groupBy('pencacah')->orderBy('pencacah', 'asc')->get();
            
        return response()->json($detailPencacah);
    }
}