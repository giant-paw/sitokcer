<?php

namespace App\Exports;

use App\Models\Distribusi\DistribusiBulanan; // <-- [GANTI MODEL]
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Illuminate\Support\Facades\DB;

class DistribusiBulananExport implements FromCollection, WithHeadings, WithMapping
{
    protected $dataRange;
    protected $dataFormat;
    protected $jenisKegiatan;
    protected $kegiatan;
    protected $search;
    protected $selectedTahun;
    protected $currentPage;
    protected $perPage;
    protected $currentModul; // <-- [TAMBAHAN]

    public function __construct($dataRange, $dataFormat, $jenisKegiatan, $kegiatan, $search, $selectedTahun, $currentPage, $perPage, $currentModul)
    {
        $this->dataRange = $dataRange;
        $this->dataFormat = $dataFormat;
        $this->jenisKegiatan = $jenisKegiatan; // Ini prefix
        $this->kegiatan = $kegiatan;      // Ini filter_value
        $this->search = $search;
        $this->selectedTahun = $selectedTahun;
        $this->currentPage = $currentPage;
        $this->perPage = $perPage;
        $this->currentModul = $currentModul;  // Simpan modul
    }

    private function buildQuery()
    {
        $prefixKegiatan = strtoupper($this->jenisKegiatan);

        $query = DistribusiBulanan::query() // <-- [GANTI MODEL]
            ->leftJoin('master_kegiatan', 'distribusi_bulanan.master_kegiatan_id', '=', 'master_kegiatan.id_master_kegiatan')
            ->where(function($q) {
                 $q->where('master_kegiatan.modul', $this->currentModul)
                   ->orWhereNull('distribusi_bulanan.master_kegiatan_id'); 
            })
            ->where(function($q) use ($prefixKegiatan) { 
                $q->where('master_kegiatan.nama_kegiatan', 'LIKE', $prefixKegiatan . '%')
                  ->orWhere(function($sub) use ($prefixKegiatan) {  
                      $sub->whereNull('distribusi_bulanan.master_kegiatan_id')
                          ->where('distribusi_bulanan.nama_kegiatan', 'LIKE', $prefixKegiatan . '%');
                  });
            })
            ->whereYear('distribusi_bulanan.created_at', $this->selectedTahun);

        if (!empty($this->kegiatan)) {
            if (is_numeric($this->kegiatan)) {
                $query->where('distribusi_bulanan.master_kegiatan_id', $this->kegiatan);
            } else {
                $query->whereNull('distribusi_bulanan.master_kegiatan_id')
                      ->where('distribusi_bulanan.nama_kegiatan', $this->kegiatan);
            }
        }

        if (!empty($this->search)) {
             $query->where(function ($q) {
                $q->where('distribusi_bulanan.BS_Responden', 'like', "%{$this->search}%")
                  ->orWhere('distribusi_bulanan.pencacah', 'like', "%{$this->search}%")
                  ->orWhere('distribusi_bulanan.pengawas', 'like', "%{$this->search}%")
                  ->orWhere('master_kegiatan.nama_kegiatan', 'like', "%{$this->search}%") 
                  ->orWhere('distribusi_bulanan.nama_kegiatan', 'like', "%{$this->search}%");
            });
        }

        $query->select('distribusi_bulanan.*')->with('masterKegiatan');
        return $query;
    }

    private function getExportData()
    {
        $query = $this->buildQuery();
        $query->latest('distribusi_bulanan.id_distribusi_bulanan');

        if ($this->dataRange == 'current_page' && $this->perPage != -1) {
            $offset = ($this->currentPage - 1) * $this->perPage;
            return $query->offset($offset)->limit($this->perPage)->get();
        }
        return $query->get();
    }
    
    public function collection()
    {
        return $this->getExportData();
    }

    public function map($item): array
    {
        $namaKegiatan = $item->masterKegiatan 
                        ? $item->masterKegiatan->nama_kegiatan 
                        : $item->nama_kegiatan;

        $targetPenyelesaian = $item->target_penyelesaian;
        $tanggalPengumpulan = $item->tanggal_pengumpulan;

        if ($this->dataFormat == 'formatted_values') {
             $targetPenyelesaian = $targetPenyelesaian ? $targetPenyelesaian->format('d/m/Y') : '-';
             $tanggalPengumpulan = $tanggalPengumpulan ? $tanggalPengumpulan->format('d/m/Y') : '-';
        } else {
             $targetPenyelesaian = $targetPenyelesaian ? $targetPenyelesaian->format('Y-m-d H:i:s') : null;
             $tanggalPengumpulan = $tanggalPengumpulan ? $tanggalPengumpulan->format('Y-m-d H:i:s') : null;
        }

        return [
            $item->id_distribusi_bulanan, // <-- [GANTI ID]
            $namaKegiatan, 
            $item->BS_Responden,
            $item->pencacah,
            $item->pengawas,
            $targetPenyelesaian,
            $item->flag_progress,
            $tanggalPengumpulan,
            $item->tahun_kegiatan ?? null,
            $item->master_kegiatan_id ?? null,
        ];
    }

    public function headings(): array
    {
        return [
            'ID Distribusi Bulanan', // <-- [GANTI HEADING]
            'Nama Kegiatan',
            'BS Responden',
            'Pencacah',
            'Pengawas',
            'Target Penyelesaian',
            'Flag Progress',
            'Tanggal Pengumpulan',
            'Tahun Kegiatan',
            'ID Master Kegiatan'
        ];
    }
}