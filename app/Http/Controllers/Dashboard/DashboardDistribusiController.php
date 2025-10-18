<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardDistribusiController extends Controller
{
    public function index()
    {
        // Menghitung statistik dari tabel tahunan
        $tahunan = DB::table('distribusi_tahunan')
            ->select(
                DB::raw("COUNT(*) as total"),
                DB::raw("SUM(CASE WHEN flag_progress = 'Selesai' THEN 1 ELSE 0 END) as selesai"),
                DB::raw("SUM(CASE WHEN flag_progress = 'Proses' THEN 1 ELSE 0 END) as proses"),
                DB::raw("SUM(CASE WHEN flag_progress = 'Belum Mulai' THEN 1 ELSE 0 END) as belum_mulai")
            )
            ->first();

        // Menghitung statistik dari tabel triwulanan
        $triwulanan = DB::table('distribusi_triwulanan')
            ->select(
                DB::raw("COUNT(*) as total"),
                DB::raw("SUM(CASE WHEN flag_progress = 'Selesai' THEN 1 ELSE 0 END) as selesai"),
                DB::raw("SUM(CASE WHEN flag_progress = 'Proses' THEN 1 ELSE 0 END) as proses"),
                DB::raw("SUM(CASE WHEN flag_progress = 'Belum Mulai' THEN 1 ELSE 0 END) as belum_mulai")
            )
            ->first();

        // Menghitung statistik dari tabel bulanan
        $bulanan = DB::table('distribusi_bulanan')
            ->select(
                DB::raw("COUNT(*) as total"),
                DB::raw("SUM(CASE WHEN flag_progress = 'Selesai' THEN 1 ELSE 0 END) as selesai"),
                DB::raw("SUM(CASE WHEN flag_progress = 'Proses' THEN 1 ELSE 0 END) as proses"),
                DB::raw("SUM(CASE WHEN flag_progress = 'Belum Mulai' THEN 1 ELSE 0 END) as belum_mulai")
            )
            ->first();

        // Menghitung total keseluruhan
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
}