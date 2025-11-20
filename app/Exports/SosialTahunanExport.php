<?php

namespace App\Exports;

use App\Models\Sosial\SosialTahunan; // <-- [GANTI]
use App\Models\Master\MasterKegiatan;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping; // Gunakan WithMapping untuk format

class SosialTahunanExport implements FromCollection, WithHeadings, WithMapping // <-- [GANTI]
{
    protected $dataRange;
    protected $dataFormat;
    protected $kegiatan;
    protected $search;
    protected $selectedTahun;
    protected $currentPage;
    protected $perPage;
    protected $currentModul;

    public function __construct($dataRange, $dataFormat, $kegiatan = null, $search = null, $selectedTahun = null, $currentPage = 1, $perPage = 20, $currentModul = null)
    {
        $this->dataRange = $dataRange;
        $this->dataFormat = $dataFormat;
        $this->kegiatan = $kegiatan;
        $this->search = $search;
        $this->selectedTahun = $selectedTahun ?? date('Y');
        $this->currentPage = (int)$currentPage;
        $this->perPage = (int)$perPage;
        $this->currentModul = $currentModul;
    }

    public function collection()
    {
        // Kueri ini harus mencerminkan kueri di Controller
        $query = SosialTahunan::query() // <-- [GANTI]
            ->leftJoin('master_kegiatan', 'sosial_tahunan.master_kegiatan_id', '=', 'master_kegiatan.id_master_kegiatan')
            ->where(function ($q) {
                $q->where('master_kegiatan.modul', $this->currentModul)
                  ->orWhereNull('sosial_tahunan.master_kegiatan_id');
            })
            ->whereYear('sosial_tahunan.created_at', $this->selectedTahun);


        // Filter berdasarkan kegiatan spesifik
        if (!empty($this->kegiatan)) {
            if (is_numeric($this->kegiatan)) {
                $query->where('sosial_tahunan.master_kegiatan_id', $this->kegiatan);
            } else {
                $query->whereNull('sosial_tahunan.master_kegiatan_id')
                      ->where('sosial_tahunan.nama_kegiatan', $this->kegiatan);
            }
        }

        // Filter berdasarkan search
        if (!empty($this->search)) {
            $searchTerm = $this->search;
            $query->where(function ($q) use ($searchTerm) {
                $q->where('sosial_tahunan.BS_Responden', 'like', "%{$searchTerm}%")
                  ->orWhere('sosial_tahunan.pencacah', 'like', "%{$searchTerm}%")
                  ->orWhere('sosial_tahunan.pengawas', 'like', "%{$searchTerm}%")
                  ->orWhere('master_kegiatan.nama_kegiatan', 'like', "%{$searchTerm}%")
                  ->orWhere('sosial_tahunan.nama_kegiatan', 'like', "%{$searchTerm}%");
            });
        }

        // Urutkan berdasarkan terbaru
        $query->latest('sosial_tahunan.id_sosial'); // <-- [GANTI]

        // Ambil data sesuai range
        if ($this->dataRange == 'current_page' && $this->perPage > 0) {
            $offset = ($this->currentPage - 1) * $this->perPage;
             return $query->select('sosial_tahunan.*')
                         ->with('masterKegiatan') // Eager load relasi
                         ->offset($offset)
                         ->limit($this->perPage)
                         ->get();
        }

        return $query->select('sosial_tahunan.*')
                    ->with('masterKegiatan') // Eager load relasi
                    ->get();
    }

    /**
     * Tentukan header untuk file export
     */
    public function headings(): array
    {
        // Header ini harus cocok dengan template import
        return [
            'nama_kegiatan',
            'bs_responden',
            'pencacah',
            'pengawas',
            'target_penyelesaian',
            'flag_progress',
            'tanggal_pengumpulan',
        ];
    }

    /**
     * Petakan data untuk setiap baris
     *
     * @param $item (SosialTahunan)
     * @return array
     */
    public function map($item): array
    {
        // Tentukan nama kegiatan yang akan ditampilkan
        $namaKegiatan = $item->masterKegiatan->nama_kegiatan ?? $item->nama_kegiatan;
        
        $targetFormat = 'Y-m-d';
        $kumpulFormat = 'Y-m-d';
        
        // Jika user pilih 'raw_values', kembalikan format database
        if ($this->dataFormat === 'raw_values') {
             $targetFormat = 'Y-m-d H:i:s';
             $kumpulFormat = 'Y-m-d H:i:s';
        }

        return [
            $namaKegiatan,
            $item->BS_Responden,
            $item->pencacah,
            $item->pengawas,
            $item->target_penyelesaian ? $item->target_penyelesaian->format($targetFormat) : null,
            $item->flag_progress,
            $item->tanggal_pengumpulan ? $item->tanggal_pengumpulan->format($kumpulFormat) : null,
        ];
    }
}