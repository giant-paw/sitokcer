<?php

namespace App\Exports;

use App\Models\Distribusi\DistribusiTriwulanan;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use PhpOffice\PhpWord\TemplateProcessor;

class DistribusiTriwulananExport implements FromCollection, WithHeadings
{
    protected $dataRange;
    protected $dataFormat;
    protected $jenisKegiatan;
    protected $kegiatan;
    protected $search;
    protected $currentPage;
    protected $perPage;

    public function __construct($dataRange, $dataFormat, $jenisKegiatan, $kegiatan = null, $search = null, $currentPage = 1, $perPage = 20)
    {
        $this->dataRange = $dataRange;
        $this->dataFormat = $dataFormat;
        $this->jenisKegiatan = $jenisKegiatan;
        $this->kegiatan = $kegiatan;
        $this->search = $search;
        $this->currentPage = $currentPage;
        $this->perPage = $perPage;
    }

    public function collection()
    {
        $query = DistribusiTriwulanan::query();

        // Filter berdasarkan jenis kegiatan (SPUNP atau SHKK)
        $query->where('nama_kegiatan', 'LIKE', strtoupper($this->jenisKegiatan) . '%');

        // Filter berdasarkan kegiatan spesifik jika ada
        if (!empty($this->kegiatan)) {
            $query->where('nama_kegiatan', $this->kegiatan);
        }

        // Filter berdasarkan search jika ada
        if (!empty($this->search)) {
            $searchTerm = $this->search;
            $query->where(function ($q) use ($searchTerm) {
                $q->where('BS_Responden', 'like', "%{$searchTerm}%")
                    ->orWhere('pencacah', 'like', "%{$searchTerm}%")
                    ->orWhere('pengawas', 'like', "%{$searchTerm}%")
                    ->orWhere('nama_kegiatan', 'like', "%{$searchTerm}%");
            });
        }

        // Urutkan berdasarkan terbaru
        $query->latest();

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
                $item->id_distribusi_triwulanan,
                $item->nama_kegiatan,
                $item->BS_Responden,
                $item->pencacah,
                $item->pengawas,
                $item->target_penyelesaian ? $item->target_penyelesaian->format('Y-m-d') : null,
                $item->flag_progress,
                $item->tanggal_pengumpulan ? $item->tanggal_pengumpulan->format('Y-m-d') : null,
                $item->tahun_kegiatan ?? null, // Jika ini integer tahun, jangan pakai format()
            ];
        });
    }

    public function headings(): array
    {
        return [
            'ID Distribusi',
            'Nama Kegiatan',
            'BS Responden',
            'Pencacah',
            'Pengawas',
            'Target Penyelesaian',
            'Flag Progress',
            'Tanggal Pengumpulan',
            'Tahun Kegiatan'
        ];
    }

    private function getDataForWord()
    {
        $query = DistribusiTriwulanan::query();

        // Filter berdasarkan jenis kegiatan
        $query->where('nama_kegiatan', 'LIKE', strtoupper($this->jenisKegiatan) . '%');

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
                    ->orWhere('nama_kegiatan', 'like', "%{$searchTerm}%");
            });
        }

        // Urutkan berdasarkan terbaru
        $query->latest();

        // Ambil data sesuai range
        if ($this->dataRange == 'current_page') {
            $offset = ($this->currentPage - 1) * $this->perPage;
            return $query->offset($offset)->limit($this->perPage)->get();
        }

        return $query->get();
    }

    public function exportToWord()
    {
        $templatePath = storage_path('templates/distribusi_triwulanan_template.docx');

        // Cek apakah template ada
        if (!file_exists($templatePath)) {
            return response()->json([
                'error' => 'Template Word tidak ditemukan di: ' . $templatePath
            ], 404);
        }

        $templateProcessor = new TemplateProcessor($templatePath);

        // Isi template dengan data
        $templateProcessor->setValue('tanggal_cetak', now()->format('d F Y'));

        // Judul laporan disesuaikan dengan filter
        $judulLaporan = 'Laporan Distribusi Triwulanan ' . strtoupper($this->jenisKegiatan);

        if (!empty($this->kegiatan)) {
            $judulLaporan .= ' - ' . $this->kegiatan;
        }

        if ($this->dataRange == 'current_page') {
            $judulLaporan .= ' (Halaman ' . $this->currentPage . ')';
        }

        $templateProcessor->setValue('judul_laporan', $judulLaporan);

        $data = $this->getDataForWord();

        $dataCount = $data->count();

        $placeholderToClone = 'id_distribusi';

        if ($dataCount > 0) {
            try {
                // Kloning baris sebanyak total data
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
                $templateProcessor->setValue('id_distribusi#' . $i, $row->id_distribusi_triwulanan ?? '');
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
            // Jika data kosong: tambahkan baris kosong
            try {
                $templateProcessor->cloneRow($placeholderToClone, 1);
                $templateProcessor->setValue('no#1', '-');
                $templateProcessor->setValue('id_distribusi#1', 'Tidak ada data');
                $templateProcessor->setValue('nama_kegiatan#1', '-');
                $templateProcessor->setValue('blok_sensus#1', '-');
                $templateProcessor->setValue('pencacahan#1', '-');
                $templateProcessor->setValue('pengawas#1', '-');
                $templateProcessor->setValue('tanggal_target#1', '-');
                $templateProcessor->setValue('flag_progress#1', '-');
                $templateProcessor->setValue('tanggal_pengumpulan#1', '-');
                $templateProcessor->setValue('tahun_kegiatan#1', '-');
            } catch (\Exception $e) {
                // Ignore jika gagal
            }
        }

        // Tentukan path untuk menyimpan file Word
        $fileName = 'DistribusiTriwulanan_' . strtoupper($this->jenisKegiatan);

        if (!empty($this->kegiatan)) {
            $fileName .= '_' . str_replace(' ', '_', $this->kegiatan);
        }

        $fileName .= '_' . date('Ymd_His') . '.docx';

        $filePath = storage_path('exports/' . $fileName);

        // Pastikan folder 'exports' dapat diakses
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

        // Kembalikan file untuk didownload
        return response()->download($filePath)->deleteFileAfterSend(true);
    }
}