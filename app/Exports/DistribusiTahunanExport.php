<?php

namespace App\Exports;

use App\Models\Distribusi\DistribusiTahunan;
// Maatwebsite\Excel digunakan untuk Excel, tapi saya biarkan karena class ini mengimplementasikannya
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use PhpOffice\PhpWord\TemplateProcessor;

class DistribusiTahunanExport implements FromCollection, WithHeadings, WithMapping
{
    protected $dataRange;
    protected $dataFormat;

    public function __construct($dataRange, $dataFormat)
    {
        $this->dataRange = $dataRange;
        $this->dataFormat = $dataFormat;
    }

    // Mengambil data sesuai dengan range yang dipilih
    public function collection()
    {
        $query = DistribusiTahunan::query();

        // Implementasi sederhana: ambil semua data. Jika data terlalu besar,
        // pertimbangkan untuk memfilter berdasarkan $this->dataRange (misalnya, tahun tertentu).
        return $query->get();
    }

    // Bagian ini hanya relevan jika Anda juga mengekspor ke Excel
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

    // Bagian ini hanya relevan jika Anda juga mengekspor ke Excel
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

    /**
     * Ekspor data ke Word menggunakan PhpWord\TemplateProcessor.
     */
    public function exportToWord()
    {
        // **SOLUSI 1: Meningkatkan Batas Waktu Eksekusi**
        // Memberikan waktu eksekusi hingga 5 menit untuk data besar.
        set_time_limit(300);

        $templatePath = storage_path('exports/template.docx');

        if (!file_exists($templatePath)) {
            return response()->json(['error' => 'File template Word tidak ditemukan di: ' . $templatePath], 404);
        }

        $templateProcessor = new TemplateProcessor($templatePath);

        // --- Mengisi Variabel Statis (Non-Cloning) ---
        try {
            $templateProcessor->setValue('tanggal_cetak', now()->format('d F Y'));
            $templateProcessor->setValue('judul_laporan', 'Laporan Distribusi Tahunan');
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Gagal mengisi variabel statis di template. Pastikan nama placeholder yang Anda gunakan ada dan tidak mengandung markup yang tersembunyi.',
                'details' => $e->getMessage()
            ], 500);
        }


        // --- Penanganan Kloning dan Data Kosong ---
        $data = $this->collection();
        $dataCount = $data->count();
        // **PERUBAHAN DISINI:** Menggunakan id_distribusi sebagai penanda kloning
        $placeholderToClone = 'id_distribusi';

        if ($dataCount > 0) {
            try {
                // Kloning baris sebanyak total data
                $templateProcessor->cloneRow($placeholderToClone, $dataCount);
            } catch (\Exception $e) {
                // Error ini menunjukkan masalah pada template Word (markup, spasi, atau placeholder tidak ditemukan)
                return response()->json([
                    // Pesan error diperbarui untuk mencerminkan variabel yang baru
                    'error' => 'Gagal mengkloning baris. Pastikan placeholder `' . $placeholderToClone . '` ada di dalam baris tabel template Word dan TIDAK mengandung spasi, enter, atau markup lain di SEL tersebut.',
                    'details' => 'Can not clone row. template variable not found or variable contains markup.'
                ], 500);
            }

            // --- Mengisi Nilai di Dalam Loop ---
            foreach ($data as $index => $row) {
                $i = $index + 1;

                // Menggunakan operator null coalescing (?? '') untuk mencegah nilai NULL
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
            // Jika data kosong: Hapus baris kloning sepenuhnya.
            $templateProcessor->deleteBlock($placeholderToClone);
        }

        // Tentukan path untuk menyimpan file Word
        $fileName = 'DistribusiTahunan_' . time() . '.docx';
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
                'error' => 'Gagal menyimpan file Word. Kemungkinan besar disebabkan oleh korupsi XML di template Anda.',
                'details' => $e->getMessage()
            ], 500);
        }


        // Kembalikan file untuk didownload
        return response()->download($filePath)->deleteFileAfterSend(true);
    }
}
