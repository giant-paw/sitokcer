<?php

namespace App\Exports;

use App\Models\Distribusi\DistribusiTahunan;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use PhpOffice\PhpWord\TemplateProcessor;

class DistribusiTahunanExport implements FromCollection, WithHeadings, WithMapping
{
    protected $dataRange;
    protected $dataFormat;
    protected $kegiatan;
    protected $search;
    protected $currentPage;
    protected $perPage;

    // PERBAIKAN 1: Tambahkan semua parameter yang diperlukan
    public function __construct($dataRange, $dataFormat, $kegiatan = null, $search = null, $currentPage = 1, $perPage = 20)
    {
        $this->dataRange = $dataRange;
        $this->dataFormat = $dataFormat;
        $this->kegiatan = $kegiatan;
        $this->search = $search;
        $this->currentPage = $currentPage;
        $this->perPage = $perPage;
    }

    // PERBAIKAN 2: Fix logika collection
    public function collection()
    {
        $query = DistribusiTahunan::query()->latest();

        // Filter berdasarkan kegiatan
        if ($this->kegiatan) {
            $query->where('nama_kegiatan', $this->kegiatan);
        }

        // Filter berdasarkan search
        if ($this->search) {
            $searchTerm = $this->search;
            $query->where(function ($q) use ($searchTerm) {
                $q->where('BS_Responden', 'like', "%{$searchTerm}%")
                    ->orWhere('pencacah', 'like', "%{$searchTerm}%")
                    ->orWhere('pengawas', 'like', "%{$searchTerm}%")
                    ->orWhere('nama_kegiatan', 'like', "%{$searchTerm}%");
            });
        }

        // PERBAIKAN 3: Cek dataRange dengan benar
        if ($this->dataRange == 'current_page') {
            // Ambil data halaman aktif dengan skip dan take
            $offset = ($this->currentPage - 1) * $this->perPage;
            return $query->skip($offset)->take($this->perPage)->get();
        }

        // Export semua data (tanpa pagination)
        return $query->get();
    }

    public function headings(): array
    {
        return [
            'No',
            'ID Kegiatan',
            'Nama Kegiatan',
            'Blok Sensus/Responden',
            'Pencacah',
            'Pengawas',
            'Tanggal Target Penyelesaian',
            'Flag Progress',
            'Tanggal Pengumpulan',
            'Tahun Kegiatan',
        ];
    }

    public function map($row): array
    {
        static $no = 0;
        $no++;

        return [
            $no,
            $row->id_distribusi ?? '',
            $row->nama_kegiatan ?? '',
            $row->BS_Responden ?? '',
            $row->pencacah ?? '',
            $row->pengawas ?? '',
            $row->target_penyelesaian ?? '',
            $row->flag_progress ?? '',
            $row->tanggal_pengumpulan ?? '',
            $row->tahun_kegiatan ?? '',
        ];
    }

    public function exportToWord()
    {
        set_time_limit(300);
        $templatePath = storage_path('exports/template.docx');

        if (!file_exists($templatePath)) {
            return response()->json(['error' => 'File template Word tidak ditemukan di: ' . $templatePath], 404);
        }

        $templateProcessor = new TemplateProcessor($templatePath);

        try {
            $templateProcessor->setValue('tanggal_cetak', now()->format('d F Y'));
            $templateProcessor->setValue('judul_laporan', 'Laporan Distribusi Tahunan');
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Gagal mengisi variabel statis di template.',
                'details' => $e->getMessage()
            ], 500);
        }

        $data = $this->collection();
        $dataCount = $data->count();
        $placeholderToClone = 'id_distribusi';

        if ($dataCount > 0) {
            try {
                $templateProcessor->cloneRow($placeholderToClone, $dataCount);
            } catch (\Exception $e) {
                return response()->json([
                    'error' => 'Gagal mengkloning baris. Pastikan placeholder `' . $placeholderToClone . '` ada di dalam baris tabel template Word.',
                    'details' => $e->getMessage()
                ], 500);
            }

            foreach ($data as $index => $row) {
                $i = $index + 1;
                $templateProcessor->setValue('no#' . $i, $i);
                $templateProcessor->setValue('id_distribusi#' . $i, $row->id_distribusi ?? '');
                $templateProcessor->setValue('nama_kegiatan#' . $i, $row->nama_kegiatan ?? '');
                $templateProcessor->setValue('blok_sensus#' . $i, $row->BS_Responden ?? '');
                $templateProcessor->setValue('pencacahan#' . $i, $row->pencacah ?? '');
                $templateProcessor->setValue('pengawas#' . $i, $row->pengawas ?? '');
                $templateProcessor->setValue('tanggal_target#' . $i, $row->target_penyelesaian ?? '');
                $templateProcessor->setValue('flag_progress#' . $i, $row->flag_progress ?? '');
                $templateProcessor->setValue('tanggal_pengumpulan#' . $i, $row->tanggal_pengumpulan ?? '');
                $templateProcessor->setValue('tahun_kegiatan#' . $i, $row->tahun_kegiatan ?? '');
            }
        } else {
            $templateProcessor->deleteBlock($placeholderToClone);
        }

        $fileName = 'DistribusiTahunan_' . time() . '.docx';
        $filePath = storage_path('exports/' . $fileName);

        if (!is_dir(storage_path('exports'))) {
            mkdir(storage_path('exports'), 0775, true);
        }

        try {
            $templateProcessor->saveAs($filePath);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Gagal menyimpan file Word.',
                'details' => $e->getMessage()
            ], 500);
        }

        return response()->download($filePath)->deleteFileAfterSend(true);
    }
}
