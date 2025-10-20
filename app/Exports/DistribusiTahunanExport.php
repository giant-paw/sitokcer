<?php

namespace App\Exports;

use App\Models\Distribusi\DistribusiTahunan;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;



class DistribusiTahunanExport implements FromCollection, WithHeadings, WithMapping
{
    protected $dataRange;
    protected $dataFormat;

    public function __construct($dataRange, $dataFormat)
    {
        $this->dataRange = $dataRange;
        $this->dataFormat = $dataFormat;
    }

    public function collection()
    {
        // Mengambil data sesuai dengan jangkauan yang dipilih
        $query = DistribusiTahunan::query();

        if ($this->dataRange == 'current_page') {
            return $query->paginate(20);  // Mengambil data halaman terkini
        }

        return $query->get();  // Mengambil semua data
    }

    public function headings(): array
    {
        return [
            'Nama Kegiatan',
            'Blok Sensus/Responden',
            'Pencacah',
            'Pengawas',
            'Tanggal Target Penyelesaian',
            'Flag Progress',
            'Tanggal Pengumpulan',
        ];
    }

    public function map($row): array
    {
        return [
            $row->nama_kegiatan,
            $row->BS_Responden,
            $row->pencacah,
            $row->pengawas,
            $row->target_penyelesaian,
            $row->flag_progress,
            $row->tanggal_pengumpulan,
        ];
    }
}
