<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardSosialController extends Controller
{
    public function index()
    {
        // Fungsi pembantu untuk mengambil statistik
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

        // Mengambil data dari setiap tabel
        $tahunan = $getStats('sosial_tahunan');
        $triwulanan = $getStats('sosial_triwulanan');
        $semesteran = $getStats('sosial_semesteran');

        // Menghitung total keseluruhan
        $total_semua = ($tahunan->total ?? 0) + ($triwulanan->total ?? 0) + ($semesteran->total ?? 0);
        $total_selesai = ($tahunan->selesai ?? 0) + ($triwulanan->selesai ?? 0) + ($semesteran->selesai ?? 0);
        $total_proses = ($tahunan->proses ?? 0) + ($triwulanan->proses ?? 0) + ($semesteran->proses ?? 0);
        $total_belum_mulai = ($tahunan->belum_mulai ?? 0) + ($triwulanan->belum_mulai ?? 0) + ($semesteran->belum_mulai ?? 0);

        return view('dashboard.sosial', compact(
            'tahunan',
            'triwulanan',
            'semesteran',
            'total_semua',
            'total_selesai',
            'total_proses',
            'total_belum_mulai'
        ));
    }
}