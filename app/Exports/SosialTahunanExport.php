<?php

namespace App\Exports;

use App\Models\Sosial\SosialTahunan;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Carbon\Carbon;

class SosialTahunanExport implements FromQuery, WithHeadings, WithMapping
{
    protected $dataRange;
    protected $dataFormat; // Tetap ada walau null
    protected $kegiatan;
    protected $search;
    protected $tahun;
    protected $currentPage;
    protected $perPage;

    public function __construct($dataRange, $dataFormat, $kegiatan, $search, $tahun, $currentPage, $perPage)
    {
        $this->dataRange = $dataRange;
        $this->dataFormat = $dataFormat; // Tidak terpakai
        $this->kegiatan = $kegiatan;
        $this->search = $search;
        $this->tahun = $tahun ?? date('Y');
        $this->currentPage = $currentPage;
        $this->perPage = ($perPage == 'all' || $perPage <= 0) ? -1 : $perPage;
    }

    public function query()
    {
        $query = SosialTahunan::query()
            ->whereYear('created_at', $this->tahun);

        if (!empty($this->kegiatan)) {
            $query->where('nama_kegiatan', $this->kegiatan);
        }

        if (!empty($this->search)) {
            $query->where(function ($q) {
                $q->where('BS_Responden', 'like', "%{$this->search}%")
                    ->orWhere('pencacah', 'like', "%{$this->search}%")
                    ->orWhere('pengawas', 'like', "%{$this->search}%")
                    ->orWhere('nama_kegiatan', 'like', "%{$this->search}%");
            });
        }

        $query->latest('id_sosial');

        if ($this->dataRange == 'current_page' && $this->perPage != -1) {
            $offset = ($this->currentPage - 1) * $this->perPage;
            $query->skip($offset)->take($this->perPage);
        }
        
        return $query;
    }

    /**
    * @var SosialTahunan $row
    */
    public function map($row): array
    {
        // [FIX] Format tanggal di sini
        return [
            $row->nama_kegiatan,
            $row->BS_Responden,
            $row->pencacah,
            $row->pengawas,
            $row->target_penyelesaian ? Carbon::parse($row->target_penyelesaian)->format('d/m/Y') : '-',
            $row->flag_progress,
            $row->tanggal_pengumpulan ? Carbon::parse($row->tanggal_pengumpulan)->format('d/m/Y') : '-',
        ];
    }

    public function headings(): array
    {
        // [FIX] Header disesuaikan (tanpa ID)
        return [
            'Nama Kegiatan',
            'BS/Responden',
            'Pencacah',
            'Pengawas',
            'Target Selesai',
            'Progress',
            'Tgl Kumpul'
        ];
    }
}