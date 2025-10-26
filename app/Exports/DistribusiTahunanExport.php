<?php

namespace App\Exports;

use App\Models\Distribusi\DistribusiTahunan;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use Carbon\Carbon; // PENTING

class DistribusiTahunanExport implements FromCollection, WithHeadings, WithMapping, WithColumnFormatting
{
    protected $dataRange;
    protected $dataFormat;
    protected $kegiatan;
    protected $search;
    protected $selectedTahun; 
    protected $currentPage;
    protected $perPage;

    public function __construct($dataRange, $dataFormat, $kegiatan = null, $search = null, $currentPage = 1, $perPage = 20, $selectedTahun = null)
    {
        $this->dataRange = $dataRange;
        $this->dataFormat = $dataFormat;
        $this->kegiatan = $kegiatan;
        $this->search = $search;
        $this->selectedTahun = $selectedTahun ?? date('Y');
        $this->currentPage = (int)$currentPage;
        $this->perPage = (int)$perPage;
    }

    public function collection()
    {
        $query = DistribusiTahunan::query();

        $query->whereYear('created_at', $this->selectedTahun);

        if (!empty($this->kegiatan)) {
            $query->where('nama_kegiatan', $this->kegiatan);
        }

        if (!empty($this->search)) {
            $searchTerm = $this->search;
            $query->where(function ($q) use ($searchTerm) {
                $q->where('BS_Responden', 'like', "%{$searchTerm}%")
                    ->orWhere('pencacah', 'like', "%{$searchTerm}%")
                    ->orWhere('pengawas', 'like', "%{$searchTerm}%")
                    ->orWhere('nama_kegiatan', 'like', "%{$searchTerm}%");
            });
        }

        $query->latest('id_distribusi');

        if ($this->dataRange == 'current_page' && $this->perPage > 0) {
            $offset = ($this->currentPage - 1) * $this->perPage;
            return $query->offset($offset)->limit($this->perPage)->get();
        }

        return $query->get();
    }

    public function headings(): array
    {
        return [
            'Nama Kegiatan',
            'BS Responden',
            'Pencacah',
            'Pengawas',
            'Target Penyelesaian',
            'Flag Progress',
            'Tanggal Pengumpulan',
        ];
    }

    /**
     * @var DistribusiTahunan $row
     */
    public function map($row): array
    {
        // Jika user minta 'raw_values', kembalikan data mentah (termasuk 00:00:00)
        if ($this->dataFormat === 'raw_values') {
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

        // --- PERBAIKAN FORMAT TANGGAL ---
        // Paksa parsing tanggal sebelum konversi ke Excel
        $target = null;
        if ($row->target_penyelesaian) {
            try {
                // Parse string/object TANGGAL (bukan datetime) lalu konversi
                $target = Date::dateTimeToExcel(Carbon::parse($row->target_penyelesaian)->startOfDay());
            } catch (\Exception $e) {
                $target = $row->target_penyelesaian; // fallback
            }
        }

        $kumpul = null;
        if ($row->tanggal_pengumpulan) {
             try {
                $kumpul = Date::dateTimeToExcel(Carbon::parse($row->tanggal_pengumpulan)->startOfDay());
            } catch (\Exception $e) {
                $kumpul = $row->tanggal_pengumpulan; // fallback
            }
        }
        
        return [
            $row->nama_kegiatan,
            $row->BS_Responden,
            $row->pencacah,
            $row->pengawas,
            $target, // Kirim nilai yang sudah dikonversi
            $row->flag_progress,
            $kumpul, // Kirim nilai yang sudah dikonversi
        ];
    }

    public function columnFormats(): array
    {
        if ($this->dataFormat === 'raw_values') {
            return [];
        }

        // Terapkan format 'dd/mm/yyyy' ke kolom E (Target) dan G (Kumpul)
        return [
            'E' => NumberFormat::FORMAT_DATE_DDMMYYYY,
            'G' => NumberFormat::FORMAT_DATE_DDMMYYYY,
        ];
    }
}