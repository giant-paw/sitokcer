<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class DashboardSosialController extends Controller
{
    public function index()
    {
        $getStats = function ($table) {
            return DB::table($table)
                ->select(
                    DB::raw("COUNT(*) as total"),
                    DB::raw("SUM(CASE WHEN flag_progress = 'Selesai' THEN 1 ELSE 0 END) as selesai"),
                    DB::raw("SUM(CASE WHEN flag_progress = 'Proses' THEN 1 ELSE 0 END) as proses"),
                    DB::raw("SUM(CASE WHEN flag_progress = 'Belum Mulai' THEN 1 ELSE 0 END) as belum_mulai")
                )
                ->first();
        };

        $tahunan = $getStats('sosial_tahunan');
        $semesteran = $getStats('sosial_semesteran');
        $triwulanan = $getStats('sosial_triwulanan');

        $total_semua = ($tahunan->total ?? 0) + ($semesteran->total ?? 0) + ($triwulanan->total ?? 0);
        $total_selesai = ($tahunan->selesai ?? 0) + ($semesteran->selesai ?? 0) + ($triwulanan->selesai ?? 0);
        $total_proses = ($tahunan->proses ?? 0) + ($semesteran->proses ?? 0) + ($triwulanan->proses ?? 0);
        $total_belum_mulai = ($tahunan->belum_mulai ?? 0) + ($semesteran->belum_mulai ?? 0) + ($triwulanan->belum_mulai ?? 0);

        // Data untuk grafik
        $chartData = [
            'labels' => ['Tahunan', 'Semesteran', 'Triwulanan'],
            'selesai' => [$tahunan->selesai ?? 0, $semesteran->selesai ?? 0, $triwulanan->selesai ?? 0],
            'proses' => [$tahunan->proses ?? 0, $semesteran->proses ?? 0, $triwulanan->proses ?? 0],
            'belum' => [$tahunan->belum_mulai ?? 0, $semesteran->belum_mulai ?? 0, $triwulanan->belum_mulai ?? 0],
        ];

        return view('dashboard.sosial', compact(
            'tahunan',
            'semesteran',
            'triwulanan',
            'total_semua',
            'total_selesai',
            'total_proses',
            'total_belum_mulai',
            'chartData'
        ));
    }
}
