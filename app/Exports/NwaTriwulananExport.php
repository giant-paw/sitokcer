<?php

namespace App\Exports;

use App\Models\Nwa\NwaTriwulanan;
use App\Models\Master\MasterKegiatan;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Carbon\Carbon;

class NwaTriwulananExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize
{
    protected $dataRange;
    protected $dataFormat;
    protected $jenisKegiatan;
    protected $kegiatan;
    protected $search;
    protected $page;
    protected $perPage;
    protected $tahun;
    protected $currentModul = 'nwa_triwulanan';

    public function __construct($dataRange, $dataFormat, $jenisKegiatan, $kegiatan, $search, $page, $perPage, $tahun)
    {
        $this->dataRange = $dataRange;
        $this->dataFormat = $dataFormat;
        $this->jenisKegiatan = $jenisKegiatan;
        $this->kegiatan = $kegiatan;
        $this->search = $search;
        $this->page = $page;
        $this->perPage = $perPage;
        $this->tahun = $tahun;
    }

    private function getPrefixMap($jenisKegiatan)
    {
        $map = [
            'sklnp'       => ['SKLNPRT-', 'SKLNP-'],
            'snaper'      => ['SNAPER-'],
            'sktnp'       => ['SKTNP TAHAP '],
        ];
        
        $jenisKegiatanLower = strtolower($jenisKegiatan);
        if (!isset($map[$jenisKegiatanLower])) {
            return [];
        }
        return $map[$jenisKegiatanLower];
    }

    public function query()
    {
        $validPrefixes = $this->getPrefixMap($this->jenisKegiatan);
        
        $query = NwaTriwulanan::query()
            ->leftJoin('master_kegiatan', 'nwa_triwulanan.master_kegiatan_id', '=', 'master_kegiatan.id_master_kegiatan')
            ->where(function($q) {
                $q->where('master_kegiatan.modul', $this->currentModul)
                  ->orWhereNull('nwa_triwulanan.master_kegiatan_id'); 
            })
            ->where(function($q) use ($validPrefixes) {
                if (empty($validPrefixes)) { $q->whereRaw('1 = 0'); return; }
                foreach ($validPrefixes as $prefix) {
                    $q->orWhere('master_kegiatan.nama_kegiatan', 'LIKE', $prefix . '%')
                      ->orWhere(function($sub) use ($prefix) {
                          $sub->whereNull('nwa_triwulanan.master_kegiatan_id')
                              ->where('nwa_triwulanan.nama_kegiatan', 'LIKE', $prefix . '%');
                      });
                }
            })
            ->whereYear('nwa_triwulanan.created_at', $this->tahun);

        if ($this->kegiatan) {
            if (is_numeric($this->kegiatan)) {
                $query->where('nwa_triwulanan.master_kegiatan_id', $this->kegiatan);
            } else {
                $query->whereNull('nwa_triwulanan.master_kegiatan_id')
                      ->where('nwa_triwulanan.nama_kegiatan', $this->kegiatan);
            }
        }

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('nwa_triwulanan.BS_Responden', 'like', "%{$this->search}%")
                  ->orWhere('nwa_triwulanan.pencacah', 'like', "%{$this->search}%")
                  ->orWhere('nwa_triwulanan.pengawas', 'like', "%{$this->search}%")
                  ->orWhere('master_kegiatan.nama_kegiatan', 'like', "%{$this->search}%")
                  ->orWhere('nwa_triwulanan.nama_kegiatan', 'like', "%{$this->search}%");
            });
        }

        if ($this->dataRange === 'current_page' && $this->perPage != -1) {
            $query->skip(($this->page - 1) * $this->perPage)->take($this->perPage);
        }
        
        return $query->select('nwa_triwulanan.*')->with('masterKegiatan')
                     ->latest('nwa_triwulanan.id_nwa_triwulanan'); 
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
            $row->id_nwa_triwulanan, 
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