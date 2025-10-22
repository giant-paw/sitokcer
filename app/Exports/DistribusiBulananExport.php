<?php

namespace App\Exports;

use App\Models\Distribusi\DistribusiBulanan;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use PhpOffice\PhpWord\TemplateProcessor;
use Maatwebsite\Excel\Facades\Excel;

class DistribusiBulananExport implements FromCollection, WithHeadings
{
    protected $dataRange;
    protected $dataFormat;
    protected $jenisKegiatan;

    public function __construct($dataRange, $dataFormat, $jenisKegiatan)
    {
        $this->dataRange = $dataRange;
        $this->dataFormat = $dataFormat;
        $this->jenisKegiatan = $jenisKegiatan;
    }

    public function collection()
    {
        $query = DistribusiBulanan::query();

        // Pastikan query membatasi data sesuai dengan jenis_kegiatan yang aktif
        $query->where('nama_kegiatan', 'LIKE', strtoupper($this->jenisKegiatan) . '%');

        // Filter berdasarkan jangkauan data (current_page atau all)
        if ($this->dataRange == 'current_page') {
            return $query->get(); // Ambil data sesuai halaman yang sedang aktif (ganti paginate dengan get)
        }

        return $query->get(); // Ambil semua data
    }

    public function headings(): array
    {
        return [
            'ID Kegiatan',
            'Nama Kegiatan',
            'Blok Responden',
            'Pencacah',
            'Pengawas',
            'Tanggal Penyelesaian',
            'Flag Progress',
            'Tanggal Pengumpulan'
        ];
    }

    // Ekspor data ke Excel
    public function exportToExcel()
    {
        return Excel::download($this, 'distribusi_bulanan.xlsx');
    }

    // Ekspor data ke CSV
    public function exportToCSV()
    {
        return response()->stream(function () {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, $this->headings()); // Menulis header kolom

            foreach ($this->collection() as $item) {
                fputcsv($handle, [
                    $item->id_distribusi_bulanan,
                    $item->nama_kegiatan,
                    $item->BS_Responden,
                    $item->pencacah,
                    $item->pengawas,
                    $item->target_penyelesaian,
                    $item->flag_progress,
                    $item->tanggal_pengumpulan,
                ]);
            }

            fclose($handle);
        }, 200, [
            "Content-Type" => "text/csv",
            "Content-Disposition" => "attachment; filename=distribusi_bulanan.csv"
        ]);
    }

    // Ekspor data ke Word
    public function exportToWord()
    {
        $templateProcessor = new TemplateProcessor(storage_path('templates/distribusi_bulanan_template.docx'));

        // Isi template dengan data
        $templateProcessor->setValue('tanggal_cetak', now()->format('d F Y'));
        $templateProcessor->setValue('judul_laporan', 'Laporan Distribusi Bulanan');

        $data = $this->collection();
        $dataCount = $data->count();
        $placeholderToClone = 'id_distribusi';

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
                $templateProcessor->setValue('id_distribusi#' . $i, $row->id_distribusi ?? '');
                $templateProcessor->setValue('nama_kegiatan#' . $i, $row->nama_kegiatan ?? '');
                $templateProcessor->setValue('blok_sensus#' . $i, $row->BS_Responden ?? '');
                $templateProcessor->setValue('pencacahan#' . $i, $row->pencacah ?? '');
                $templateProcessor->setValue('pengawas#' . $i, $row->pengawas ?? '');
                $templateProcessor->setValue('tanggal_target#' . $i, $row->target_penyelesaian ?? '');
                $templateProcessor->setValue('flag_progress#' . $i, $row->flag_progress ?? '');
                $templateProcessor->setValue('tanggal_pengumpulan#' . $i, $row->tanggal_pengumpulan ?? '');
            }
        } else {
            $templateProcessor->deleteBlock($placeholderToClone);
        }

        $fileName = 'DistribusiBulanan_' . time() . '.docx';
        $filePath = storage_path('exports/' . $fileName);

        if (!is_dir(storage_path('exports'))) {
            mkdir(storage_path('exports'), 0775, true);
        }

        try {
            $templateProcessor->saveAs($filePath);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Gagal menyimpan file Word. Kemungkinan besar disebabkan oleh korupsi XML di template Anda.',
                'details' => $e->getMessage()
            ], 500);
        }

        return response()->download($filePath)->deleteFileAfterSend(true);
    }
}
