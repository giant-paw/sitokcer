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
            return $query->offset($offset)->limit($this->perPage)->get();
        }

        // Jika dataRange = 'all', ambil semua data
        return $query->get();
    }

    public function headings(): array
    {
        return [
            'ID NWA',
            'Nama Kegiatan',
            'BS Responden',
            'Pencacah',
            'Pengawas',
            'Target Penyelesaian',
            'Flag Progress',
            'Tanggal Pengumpulan',
        ];
    }

    public function exportToWord()
    {
        $templatePath = storage_path('templates/nwa_tahunan_template.docx');

        if (!file_exists($templatePath)) {
            return response()->json([
                'error' => 'Template Word tidak ditemukan di: ' . $templatePath
            ], 404);
        }

        $templateProcessor = new TemplateProcessor($templatePath);

        // Set tanggal cetak
        $templateProcessor->setValue('tanggal_cetak', now()->format('d F Y'));

        // Set judul laporan dengan filter yang aktif
        $judulLaporan = 'Laporan NWA Tahunan Tahun ' . $this->tahun;
        if (!empty($this->kegiatan)) {
            $judulLaporan .= ' - ' . $this->kegiatan;
        }
        if ($this->dataRange == 'current_page') {
            $judulLaporan .= ' (Halaman ' . $this->currentPage . ')';
        }

        $templateProcessor->setValue('judul_laporan', $judulLaporan);

        $data = $this->collection();
        $dataCount = $data->count();

        $placeholderToClone = 'id_nwa';

        if ($dataCount > 0) {
            try {
                $templateProcessor->cloneRow($placeholderToClone, $dataCount);
            } catch (\Exception $e) {
                return response()->json([
                    'error' => 'Gagal mengkloning baris di template. Pastikan placeholder `' . $placeholderToClone . '` ada di template Word.',
                    'details' => $e->getMessage()
                ], 500);
            }

            foreach ($data as $index => $row) {
                $i = $index + 1;
                $templateProcessor->setValue('no#' . $i, $i);
                $templateProcessor->setValue('id_nwa#' . $i, $row->id_nwa ?? '');
                $templateProcessor->setValue('nama_kegiatan#' . $i, $row->nama_kegiatan ?? '');
                $templateProcessor->setValue('blok_sensus#' . $i, $row->BS_Responden ?? '');
                $templateProcessor->setValue('pencacahan#' . $i, $row->pencacah ?? '');
                $templateProcessor->setValue('pengawas#' . $i, $row->pengawas ?? '');
                $templateProcessor->setValue('tanggal_target#' . $i, $row->target_penyelesaian ?? '');
                $templateProcessor->setValue('flag_progress#' . $i, $row->flag_progress ?? '');
                $templateProcessor->setValue('tanggal_pengumpulan#' . $i, $row->tanggal_pengumpulan ?? '');
            }
        } else {
            // Jika data kosong, tambahkan baris "Tidak ada data"
            try {
                $templateProcessor->cloneRow($placeholderToClone, 1);
                $templateProcessor->setValue('no#1', '-');
                $templateProcessor->setValue('id_nwa#1', 'Tidak ada data');
                $templateProcessor->setValue('nama_kegiatan#1', '-');
                $templateProcessor->setValue('blok_sensus#1', '-');
                $templateProcessor->setValue('pencacahan#1', '-');
                $templateProcessor->setValue('pengawas#1', '-');
                $templateProcessor->setValue('tanggal_target#1', '-');
                $templateProcessor->setValue('flag_progress#1', '-');
                $templateProcessor->setValue('tanggal_pengumpulan#1', '-');
            } catch (\Exception $e) {
                // Ignore jika gagal
            }
        }

        // Generate nama file dengan filter
        $fileName = 'NWA_Tahunan';
        if (!empty($this->kegiatan)) {
            $fileName .= '_' . str_replace(' ', '_', $this->kegiatan);
        }
        $fileName .= '_' . date('Ymd_His') . '.docx';

        $filePath = storage_path('exports/' . $fileName);

        // Pastikan folder exports ada
        if (!is_dir(storage_path('exports'))) {
            mkdir(storage_path('exports'), 0775, true);
        }

        // Simpan file Word
        try {
            $templateProcessor->saveAs($filePath);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Gagal menyimpan file Word.',
                'details' => $e->getMessage()
            ], 500);
        }

        // Download file dan hapus setelah download
        return response()->download($filePath)->deleteFileAfterSend(true);
    }
}
