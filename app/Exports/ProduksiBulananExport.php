<?php

namespace App\Exports;

use App\Models\Produksi\ProduksiBulanan;
use App\Models\Master\MasterKegiatan;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Carbon\Carbon;

class ProduksiBulananExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize
{
    protected $dataRange;
    protected $dataFormat;
    protected $jenisKegiatan;
    protected $kegiatan;
    protected $search;
    protected $page;
    protected $perPage;
    protected $tahun;
    protected $currentModul = 'produksi_bulanan';

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
            'ksa-padi'           => ['KSAPadi-'],
            'ksa-jagung'         => ['KSAJagung-'],
            'lptb'               => ['LPTB-'],
            'sphsbs'             => ['SPHSBS-'],
            'sp-palawija'        => ['SPPalawija-'],
            'perkebunan-bulanan' => ['PerkebunanBulanan-'],
            'ibs-bulanan'        => ['IBSBulanan-']
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
        
        $query = ProduksiBulanan::query()
            ->leftJoin('master_kegiatan', 'produksi_bulanan.master_kegiatan_id', '=', 'master_kegiatan.id_master_kegiatan')
            ->where(function($q) {
                $q->where('master_kegiatan.modul', $this->currentModul)
                  ->orWhereNull('produksi_bulanan.master_kegiatan_id'); 
            })
            ->where(function($q) use ($validPrefixes) {
                if (empty($validPrefixes)) { $q->whereRaw('1 = 0'); return; }
                foreach ($validPrefixes as $prefix) {
                    $q->orWhere('master_kegiatan.nama_kegiatan', 'LIKE', $prefix . '%')
                      ->orWhere(function($sub) use ($prefix) {
                          $sub->whereNull('produksi_bulanan.master_kegiatan_id')
                              ->where('produksi_bulanan.nama_kegiatan', 'LIKE', $prefix . '%');
                      });
                }
            })
            ->whereYear('produksi_bulanan.created_at', $this->tahun);

        if ($this->kegiatan) {
            if (is_numeric($this->kegiatan)) {
                $query->where('produksi_bulanan.master_kegiatan_id', $this->kegiatan);
            } else {
                $query->whereNull('produksi_bulanan.master_kegiatan_id')
                      ->where('produksi_bulanan.nama_kegiatan', $this->kegiatan);
            }
        }

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('produksi_bulanan.BS_Responden', 'like', "%{$this->search}%")
                  ->orWhere('produksi_bulanan.pencacah', 'like', "%{$this->search}%")
                  ->orWhere('produksi_bulanan.pengawas', 'like', "%{$this->search}%")
                  ->orWhere('master_kegiatan.nama_kegiatan', 'like', "%{$this->search}%")
                  ->orWhere('produksi_bulanan.nama_kegiatan', 'like', "%{$this->search}%");
            });
        }

        if ($this->dataRange === 'current_page' && $this->perPage != -1) {
            $query->skip(($this->page - 1) * $this->perPage)->take($this->perPage);
        }
        
        return $query->select('produksi_bulanan.*')->with('masterKegiatan')
                     ->latest('produksi_bulanan.id_produksi_bulanan');
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
            $row->id_produksi_bulanan,
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