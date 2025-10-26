<?php

namespace App\Exports;

use App\Models\Sosial\SosialTriwulanan;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Carbon\Carbon;

class SosialTriwulananExport implements FromQuery, WithHeadings, WithMapping
{
    protected $dataRange;
    protected $dataFormat;
    protected $jenisKegiatan; // Tambahkan ini
    protected $kegiatan;
    protected $search;
    protected $tahun;
    protected $currentPage;
    protected $perPage;

    // [FIX] Update constructor
    public function __construct($dataRange, $dataFormat, $jenisKegiatan, $kegiatan, $search, $tahun, $currentPage, $perPage)
    {
        $this->dataRange = $dataRange;
        $this->dataFormat = $dataFormat; // Tidak terpakai, tapi ada
        $this->jenisKegiatan = $jenisKegiatan; // 'seruti'
        $this->kegiatan = $kegiatan;
        $this->search = $search;
        $this->tahun = $tahun ?? date('Y');
        $this->currentPage = $currentPage;
        $this->perPage = ($perPage == 'all' || $perPage <= 0) ? -1 : $perPage;
    }

    public function query()
    {
        $prefixKegiatan = 'Seruti'; // Hardcode atau sesuaikan dari $jenisKegiatan

        $query = SosialTriwulanan::query()
            ->where('nama_kegiatan', 'LIKE', $prefixKegiatan . '%')
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

        $query->latest('id_sosial_triwulanan');

        if ($this->dataRange == 'current_page' && $this->perPage != -1) {
            $offset = ($this->currentPage - 1) * $this->perPage;
            $query->skip($offset)->take($this->perPage);
        }
        
        return $query;
    }

    /**
    * @var SosialTriwulanan $row
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
        // [FIX] Header disesuaikan
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