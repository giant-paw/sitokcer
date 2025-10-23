<?php

namespace App\Exports;

use App\Models\Produksi\ProduksiTahunan;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use PhpOffice\PhpWord\TemplateProcessor;

class ProduksiTahunanExport implements FromCollection, WithHeadings, WithMapping
{
    protected $dataRange;
    protected $dataFormat;
    protected $tahun;
    protected $kegiatan;
    protected $search;
    protected $currentPage;
    protected $perPage;

    /**
     * Constructor - Terima semua parameter filter dan pagination
     */
    public function __construct($dataRange, $dataFormat, $tahun = null, $kegiatan = null, $search = null, $currentPage = 1, $perPage = 20)
    {
        $this->dataRange = $dataRange;
        $this->dataFormat = $dataFormat;
        $this->tahun = $tahun ?? date('Y'); // Default tahun sekarang
        $this->kegiatan = $kegiatan;
        $this->search = $search;
        $this->currentPage = $currentPage;
        $this->perPage = $perPage;
    }

    /**
     * Ambil data sesuai filter dan pagination
     */
    public function collection()
    {
        $query = ProduksiTahunan::query()
            ->whereYear('created_at', $this->tahun)
            ->latest('id_produksi');

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

        // Cek apakah export halaman saat ini atau semua data
        if ($this->dataRange == 'current_page') {
            // Ambil data halaman aktif dengan skip dan take
            $offset = ($this->currentPage - 1) * $this->perPage;
            return $query->skip($offset)->take($this->perPage)->get();
        }

        // Export semua data (tanpa pagination)
        return $query->get();
    }

    /**
     * Header kolom untuk Excel/CSV
     */
    public function headings(): array
    {
        return [
            'No',
            'ID Produksi',
            'Nama Kegiatan',
            'Blok Sensus/Responden',
            'Pencacah',
            'Pengawas',
            'Target Penyelesaian',
            'Flag Progress',
            'Tanggal Pengumpulan',
            'Tahun Data',
        ];
    }

    /**
     * Mapping setiap baris data
     */
    public function map($row): array
    {
        static $no = 0;
        $no++;

        return [
            $no,
            $row->id_produksi ?? '',
            $row->nama_kegiatan ?? '',
            $row->BS_Responden ?? '',
            $row->pencacah ?? '',
            $row->pengawas ?? '',
            $row->target_penyelesaian ? $row->target_penyelesaian->format('Y-m-d') : '',
            $row->flag_progress ?? '',
            $row->tanggal_pengumpulan ? $row->tanggal_pengumpulan->format('Y-m-d') : '',
            $this->tahun, // Tahun dari filter
        ];
    }

    /**
     * Export ke Word menggunakan template
     */
    public function exportToWord()
    {
        set_time_limit(300);

        $templatePath = storage_path('exports/template_produksi.docx');

        if (!file_exists($templatePath)) {
            return response()->json([
                'error' => 'File template Word tidak ditemukan di: ' . $templatePath
            ], 404);
        }

        $templateProcessor = new TemplateProcessor($templatePath);

        try {
            // Set variabel statis di template
            $templateProcessor->setValue('tanggal_cetak', now()->format('d F Y'));
            $templateProcessor->setValue('judul_laporan', 'Laporan Produksi Tahunan ' . $this->tahun);
            $templateProcessor->setValue('tahun', $this->tahun);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Gagal mengisi variabel statis di template.',
                'details' => $e->getMessage()
            ], 500);
        }

        $data = $this->collection();
        $dataCount = $data->count();

        $placeholderToClone = 'id_produksi';

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
                $templateProcessor->setValue('id_produksi#' . $i, $row->id_produksi ?? '');
                $templateProcessor->setValue('nama_kegiatan#' . $i, $row->nama_kegiatan ?? '');
                $templateProcessor->setValue('blok_sensus#' . $i, $row->BS_Responden ?? '');
                $templateProcessor->setValue('pencacahan#' . $i, $row->pencacah ?? '');
                $templateProcessor->setValue('pengawas#' . $i, $row->pengawas ?? '');
                $templateProcessor->setValue('tanggal_target#' . $i, $row->target_penyelesaian ? $row->target_penyelesaian->format('Y-m-d') : '');
                $templateProcessor->setValue('flag_progress#' . $i, $row->flag_progress ?? '');
                $templateProcessor->setValue('tanggal_pengumpulan#' . $i, $row->tanggal_pengumpulan ? $row->tanggal_pengumpulan->format('Y-m-d') : '');
            }
        } else {
            // Jika tidak ada data, hapus block placeholder
            try {
                $templateProcessor->deleteBlock($placeholderToClone);
            } catch (\Exception $e) {
                // Ignore jika tidak ada block
            }
        }

        $fileName = 'ProduksiTahunan_' . $this->tahun . '_' . time() . '.docx';
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
