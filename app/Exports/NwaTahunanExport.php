<?php

namespace App\Exports;

use App\Models\Nwa\NwaTahunan;
use App\Models\Master\MasterKegiatan;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Carbon\Carbon;

class NwaTahunanExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize
{
    protected $dataRange;
    protected $dataFormat;
    protected $kegiatan;
    protected $search;
    protected $page;
    protected $perPage;
    protected $tahun;
    protected $currentModul = 'nwa_tahunan';

    public function __construct($dataRange, $dataFormat, $kegiatan, $search, $page, $perPage, $tahun)
    {
        $this->dataRange = $dataRange;
        $this->dataFormat = $dataFormat;
        $this->kegiatan = $kegiatan;
        $this->search = $search;
        $this->page = $page;
        $this->perPage = $perPage;
        $this->tahun = $tahun;
    }

    public function query()
    {
        $query = NwaTahunan::query()
            ->leftJoin('master_kegiatan', 'nwa_tahunan.master_kegiatan_id', '=', 'master_kegiatan.id_master_kegiatan')
            ->where(function($q) {
                $q->where('master_kegiatan.modul', $this->currentModul)
                  ->orWhereNull('nwa_tahunan.master_kegiatan_id'); 
            })
            ->whereYear('nwa_tahunan.created_at', $this->tahun);

        if ($this->kegiatan) {
            if (is_numeric($this->kegiatan)) {
                $query->where('nwa_tahunan.master_kegiatan_id', $this->kegiatan);
            } else {
                $query->whereNull('nwa_tahunan.master_kegiatan_id')
                      ->where('nwa_tahunan.nama_kegiatan', $this->kegiatan);
            }
        }

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('nwa_tahunan.BS_Responden', 'like', "%{$this->search}%")
                  ->orWhere('nwa_tahunan.pencacah', 'like', "%{$this->search}%")
                  ->orWhere('nwa_tahunan.pengawas', 'like', "%{$this->search}%")
                  ->orWhere('master_kegiatan.nama_kegiatan', 'like', "%{$this->search}%")
                  ->orWhere('nwa_tahunan.nama_kegiatan', 'like', "%{$this->search}%");
            });
        }

        if ($this->dataRange === 'current_page' && $this->perPage != -1) {
            $query->skip(($this->page - 1) * $this->perPage)->take($this->perPage);
        }
        
        return $query->select('nwa_tahunan.*')->with('masterKegiatan')
                     ->latest('nwa_tahunan.id_nwa'); // Ganti PK
    }

    public function headings(): array
    {
        return [
            'ID',
            'Nama Kegiatan',
            'BS/Responden',
            'Pencacah',
            'Pengawas',
            'Target Selesai',
            'Progress',
            'Tgl Kumpul',
        ];
    }

    public function map($row): array
    {
        try {
            $targetSelesai = Carbon::parse($row->target_penyelesaian)->format('d/m/Y');
        } catch (\Exception $e) {
            $targetSelesai = $row->target_penyelesaian; 
        }

        try {
            $tglKumpul = Carbon::parse($row->tanggal_pengumpulan)->format('d/m/Y');
        } catch (\Exception $e) {
            $tglKumpul = $row->tanggal_pengumpulan;
        }

        return [
            $row->id_nwa, // Ganti PK
            $row->masterKegiatan->nama_kegiatan ?? $row->nama_kegiatan, 
            $row->BS_Responden,
            $row->pencacah,
            $row->pengawas,
            $targetSelesai,
            $row->flag_progress,
            $tglKumpul,
        ];
    }
}