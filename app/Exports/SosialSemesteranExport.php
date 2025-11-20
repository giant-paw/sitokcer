<?php

namespace App\Exports;

use App\Models\Sosial\SosialSemesteran; // <-- [GANTI]
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping; 

class SosialSemesteranExport implements FromCollection, WithHeadings, WithMapping // <-- [GANTI]
{
    protected $dataRange, $dataFormat, $kegiatan, $search, $selectedTahun, $currentPage, $perPage, $currentModul;

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
        // Kueri ini SAMA DENGAN di Controller
        $query = SosialSemesteran::query() // <-- [GANTI]
            ->leftJoin('master_kegiatan', 'sosial_semesteran.master_kegiatan_id', '=', 'master_kegiatan.id_master_kegiatan')
            ->where(function ($q) {
                $q->where('master_kegiatan.modul', $this->currentModul)
                  ->orWhereNull('sosial_semesteran.master_kegiatan_id');
            })
            ->whereYear('sosial_semesteran.created_at', $this->selectedTahun);

        if (!empty($this->kegiatan)) {
            if (is_numeric($this->kegiatan)) {
                $query->where('sosial_semesteran.master_kegiatan_id', $this->kegiatan);
            } else {
                $query->whereNull('sosial_semesteran.master_kegiatan_id')
                      ->where('sosial_semesteran.nama_kegiatan', $this->kegiatan);
            }
        }

        if (!empty($this->search)) {
            $searchTerm = $this->search;
            $query->where(function ($q) use ($searchTerm) {
                $q->where('sosial_semesteran.BS_Responden', 'like', "%{$searchTerm}%")
                  ->orWhere('sosial_semesteran.pencacah', 'like', "%{$searchTerm}%")
                  ->orWhere('sosial_semesteran.pengawas', 'like', "%{$searchTerm}%")
                  ->orWhere('master_kegiatan.nama_kegiatan', 'like', "%{$searchTerm}%")
                  ->orWhere('sosial_semesteran.nama_kegiatan', 'like', "%{$searchTerm}%");
            });
        }

        $query->latest('sosial_semesteran.id_sosial_semesteran') // <-- [GANTI] PK
              ->select('sosial_semesteran.*')
              ->with('masterKegiatan'); 

        if ($this->dataRange == 'current_page' && $this->perPage > 0) {
            $offset = ($this->currentPage - 1) * $this->perPage;
             return $query->offset($offset)->limit($this->perPage)->get();
        }

        return $query->get();
    }

    public function headings(): array
    {
        return ['nama_kegiatan', 'bs_responden', 'pencacah', 'pengawas', 'target_penyelesaian', 'flag_progress', 'tanggal_pengumpulan'];
    }

    /**
     * @param $item (SosialSemesteran)
     */
    public function map($item): array
    {
        $namaKegiatan = $item->masterKegiatan->nama_kegiatan ?? $item->nama_kegiatan;
        
        $dateFormat = 'Y-m-d'; // Default 'formatted_values'
        if ($this->dataFormat === 'raw_values') {
             $dateFormat = 'Y-m-d H:i:s';
        }

        return [
            $namaKegiatan,
            $item->BS_Responden,
            $item->pencacah,
            $item->pengawas,
            $item->target_penyelesaian ? $item->target_penyelesaian->format($dateFormat) : null,
            $item->flag_progress,
            $item->tanggal_pengumpulan ? $item->tanggal_pengumpulan->format($dateFormat) : null,
        ];
    }
}