<?php

namespace App\Exports;

use App\Models\Produksi\ProduksiTriwulanan;
use App\Models\Master\MasterKegiatan;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Carbon\Carbon;

class ProduksiTriwulananExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize
{
    // Properti untuk menyimpan filter
    protected $dataRange;
    protected $dataFormat;
    protected $jenisKegiatan;
    protected $kegiatan;
    protected $search;
    protected $page;
    protected $perPage;
    protected $tahun;
    protected $currentModul = 'produksi_triwulanan';

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
            'tpi'       => ['TPI-'],
            'sktr'      => ['SKTR-'],
            'sphbst'    => ['SPHBST-'],
            'sphtbf'    => ['SPHTBF-'],
            'sphth'     => ['SPHTH-'],
            'airbersih' => ['AIRBERSIH-']
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
        
        $query = ProduksiTriwulanan::query()
            ->leftJoin('master_kegiatan', 'produksi_triwulanan.master_kegiatan_id', '=', 'master_kegiatan.id_master_kegiatan')
            ->where(function($q) {
                $q->where('master_kegiatan.modul', $this->currentModul)
                  ->orWhereNull('produksi_triwulanan.master_kegiatan_id'); 
            })
            ->where(function($q) use ($validPrefixes) {
                if (empty($validPrefixes)) { $q->whereRaw('1 = 0'); return; }
                foreach ($validPrefixes as $prefix) {
                    $q->orWhere('master_kegiatan.nama_kegiatan', 'LIKE', $prefix . '%')
                      ->orWhere(function($sub) use ($prefix) {
                          $sub->whereNull('produksi_triwulanan.master_kegiatan_id')
                              ->where('produksi_triwulanan.nama_kegiatan', 'LIKE', $prefix . '%');
                      });
                }
            })
            ->whereYear('produksi_triwulanan.created_at', $this->tahun);

        if ($this->kegiatan) {
            if (is_numeric($this->kegiatan)) {
                $query->where('produksi_triwulanan.master_kegiatan_id', $this->kegiatan);
            } else {
                $query->whereNull('produksi_triwulanan.master_kegiatan_id')
                      ->where('produksi_triwulanan.nama_kegiatan', $this->kegiatan);
            }
        }

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('produksi_triwulanan.BS_Responden', 'like', "%{$this->search}%")
                  ->orWhere('produksi_triwulanan.pencacah', 'like', "%{$this->search}%")
                  ->orWhere('produksi_triwulanan.pengawas', 'like', "%{$this->search}%")
                  ->orWhere('master_kegiatan.nama_kegiatan', 'like', "%{$this->search}%")
                  ->orWhere('produksi_triwulanan.nama_kegiatan', 'like', "%{$this->search}%");
            });
        }

        if ($this->dataRange === 'current_page' && $this->perPage != -1) {
            $query->skip(($this->page - 1) * $this->perPage)->take($this->perPage);
        }
        
        return $query->select('produksi_triwulanan.*')->with('masterKegiatan')
                     ->latest('produksi_triwulanan.id_produksi_triwulanan');
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
        $targetSelesai = $row->target_penyelesaian;
        $tglKumpul = $row->tanggal_pengumpulan;

        if ($this->dataFormat === 'formatted_values') {
            $targetSelesai = $targetSelesai ? Carbon::parse($targetSelesai)->format('d/m/Y') : '-';
            $tglKumpul = $tglKumpul ? Carbon::parse($tglKumpul)->format('d/m/Y') : '-';
        } else {
            $targetSelesai = $targetSelesai ? Carbon::parse($targetSelesai)->format('Y-m-d') : null;
            $tglKumpul = $tglKumpul ? Carbon::parse($tglKumpul)->format('Y-m-d') : null;
        }

        return [
            $row->id_produksi_triwulanan,
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