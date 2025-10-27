<?php

namespace App\Exports;

use App\Models\Nwa\NwaTahunan;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use PhpOffice\PhpWord\TemplateProcessor;

class NwaTahunanExport implements FromCollection, WithHeadings
{
    protected $dataRange;
    protected $dataFormat;
    protected $kegiatan;
    protected $search;
    protected $tahun;
    protected $currentPage;
    protected $perPage;
    public function __construct($dataRange, $dataFormat, $kegiatan = null, $search = null, $tahun = null, $currentPage = 1, $perPage = 20)
    {
        $this->dataRange = $dataRange;
        $this->dataFormat = $dataFormat;
        $this->kegiatan = $kegiatan;
        $this->search = $search;
        $this->tahun = $tahun ?? date('Y');
        $this->currentPage = $currentPage;
        $this->perPage = $perPage;
    }
    public function collection()
    {
        $query = NwaTahunan::query();
        // Filter berdasarkan tahun (menggunakan created_at)
        $query->whereYear('created_at', $this->tahun);
        // Filter berdasarkan kegiatan spesifik
        if (!empty($this->kegiatan)) {
            $query->where('nama_kegiatan', $this->kegiatan);
        }
        // Filter berdasarkan search
        if (!empty($this->search)) {
            $searchTerm = $this->search;
            $query->where(function ($q) use ($searchTerm) {
                $q->where('BS_Responden', 'like', "%{$searchTerm}%")
                    ->orWhere('pencacah', 'like', "%{$searchTerm}%")
                    ->orWhere('pengawas', 'like', "%{$searchTerm}%")
                    ->orWhere('nama_kegiatan', 'like', "%{$searchTerm}%")
                    ->orWhere('flag_progress', 'like', "%{$searchTerm}%");
            });
        }
        // Urutkan berdasarkan terbaru
        $query->latest('id_nwa');
        // Jika dataRange = 'current_page', ambil data halaman terkini saja
        if ($this->dataRange == 'current_page') {
            $offset = ($this->currentPage - 1) * $this->perPage;
            $data = $query->offset($offset)->limit($this->perPage)->get();
        } else {
            // Jika dataRange = 'all', ambil semua data
            $data = $query->get();
        }
        
        return $data->map(function ($item) {
            return [
                $item->id_nwa,
                $item->nama_kegiatan,
                $item->BS_Responden,
                $item->pencacah,
                $item->pengawas,
                $item->id_nwa, 
                $item->flag_progress,
                $item->tanggal_pengumpulan ? $item->tanggal_pengumpulan->format('Y-m-d') : null,
            ];
        });
    }
    public function headings(): array
    {
        return [
            'ID NWA',
            'Nama Kegiatan',
            'BS Responden',
            'Pencacah',
            'Pengawas',
            'ID NWA Tahunan',
            'Flag Progress',
            'Tanggal Pengumpulan',
        ];
    }
}