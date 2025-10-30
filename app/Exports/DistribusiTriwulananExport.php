<?php

namespace App\Exports;

use App\Models\Distribusi\DistribusiTriwulanan;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping; 
use PhpOffice\PhpWord\TemplateProcessor;
use Illuminate\Support\Facades\DB; 

class DistribusiTriwulananExport implements FromCollection, WithHeadings, WithMapping 
{
    protected $dataRange;
    protected $dataFormat;
    protected $jenisKegiatan;
    protected $kegiatan;
    protected $search;
    protected $currentPage;
    protected $perPage;
    protected $selectedTahun; 

    public function __construct($dataRange, $dataFormat, $jenisKegiatan, $kegiatan = null, $search = null, $currentPage = 1, $perPage = 20, $selectedTahun = null)
    {
        $this->dataRange = $dataRange;
        $this->dataFormat = $dataFormat;
        $this->jenisKegiatan = $jenisKegiatan;
        $this->kegiatan = $kegiatan;
        $this->search = $search;
        $this->currentPage = $currentPage;
        // Tentukan perPage di sini
        $this->perPage = ($perPage == 'all' || $dataRange == 'all') ? -1 : (int)$perPage;
        $this->selectedTahun = $selectedTahun ?? date('Y'); // Simpan tahun
    }

    /**
     * [PERBAIKAN 3] Query Builder Sentral
     * Logika ini disamakan PERSIS dengan di Controller index()
     */
    private function buildQuery()
    {
        $prefixKegiatan = strtoupper($this->jenisKegiatan);

        $query = DistribusiTriwulanan::query()
            ->leftJoin('master_kegiatan', 'distribusi_triwulanan.master_kegiatan_id', '=', 'master_kegiatan.id_master_kegiatan')
            ->where(function($q) use ($prefixKegiatan) {
                $q->where('master_kegiatan.nama_kegiatan', 'LIKE', $prefixKegiatan . '%')
                  ->orWhere(function($sub) use ($prefixKegiatan) {
                      $sub->whereNull('distribusi_triwulanan.master_kegiatan_id')
                          ->where('distribusi_triwulanan.nama_kegiatan', 'LIKE', $prefixKegiatan . '%');
                  });
            })
            // [FIX UTAMA] Tambahkan filter tahun yang hilang
            ->whereYear('distribusi_triwulanan.created_at', $this->selectedTahun); 

        // [FIX] Filter Kegiatan (ID atau String)
        if (!empty($this->kegiatan)) {
            if (is_numeric($this->kegiatan)) {
                $query->where('distribusi_triwulanan.master_kegiatan_id', $this->kegiatan);
            } else {
                $query->whereNull('distribusi_triwulanan.master_kegiatan_id')
                      ->where('distribusi_triwulanan.nama_kegiatan', $this->kegiatan);
            }
        }

        // [FIX] Filter Search (Kedua tabel)
        if (!empty($this->search)) {
            $searchTerm = $this->search;
            $query->where(function ($q) use ($searchTerm) {
                $q->where('distribusi_triwulanan.BS_Responden', 'like', "%{$searchTerm}%")
                  ->orWhere('distribusi_triwulanan.pencacah', 'like', "%{$searchTerm}%")
                  ->orWhere('distribusi_triwulanan.pengawas', 'like', "%{$searchTerm}%")
                  ->orWhere('master_kegiatan.nama_kegiatan', 'like', "%{$searchTerm}%")
                  ->orWhere('distribusi_triwulanan.nama_kegiatan', 'like', "%{$searchTerm}%");
            });
        }

        // Select kolom utama dan Eager Load
        $query->select('distribusi_triwulanan.*')->with('masterKegiatan');

        return $query;
    }

    /**
     * [PERBAIKAN 4] Fungsi helper untuk mengambil data
     */
    private function getExportData()
    {
        $query = $this->buildQuery(); // Panggil query builder

        // Urutkan berdasarkan terbaru
        $query->latest('distribusi_triwulanan.id_distribusi_triwulanan');

        // Terapkan 'limit' dan 'offset' hanya jika 'current_page'
        if ($this->dataRange == 'current_page' && $this->perPage != -1) {
            $offset = ($this->currentPage - 1) * $this->perPage;
            return $query->offset($offset)->limit($this->perPage)->get();
        }

        // Jika dataRange = 'all' (atau perPage = -1)
        return $query->get();
    }
    
    /**
     * [PERBAIKAN 5] 'collection()' sekarang hanya memanggil helper
     */
    public function collection()
    {
        return $this->getExportData();
    }

    /**
     * [PERBAIKAN 6] Gunakan 'map()' untuk memformat data
     * Fungsi ini akan dipanggil otomatis untuk setiap baris
     */
    public function map($item): array
    {
        // $item adalah model DistribusiTriwulanan
        
        // Logika untuk menampilkan nama yang benar (bersih atau kotor)
        // Kita menggunakan relasi 'masterKegiatan' yang sudah di-eager load
        $namaKegiatan = $item->masterKegiatan 
                        ? $item->masterKegiatan->nama_kegiatan 
                        : $item->nama_kegiatan;

        // Logika untuk format tanggal
        $targetPenyelesaian = $item->target_penyelesaian;
        $tanggalPengumpulan = $item->tanggal_pengumpulan;

        // Cek $this->dataFormat yang dikirim dari controller
        if ($this->dataFormat == 'formatted_values') {
             // Format tampilan (dd/mm/YYYY)
             $targetPenyelesaian = $targetPenyelesaian ? $targetPenyelesaian->format('d/m/Y') : '-';
             $tanggalPengumpulan = $tanggalPengumpulan ? $tanggalPengumpulan->format('d/m/Y') : '-';
        } else {
             // 'raw_values' (Y-m-d H:i:s)
             $targetPenyelesaian = $targetPenyelesaian ? $targetPenyelesaian->format('Y-m-d H:i:s') : null;
             $tanggalPengumpulan = $tanggalPengumpulan ? $tanggalPengumpulan->format('Y-m-d H:i:s') : null;
        }

        return [
            $item->id_distribusi_triwulanan,
            $namaKegiatan, // <-- Nama yang sudah diperbaiki
            $item->BS_Responden,
            $item->pencacah,
            $item->pengawas,
            $targetPenyelesaian, // <-- Tanggal yang sudah diformat
            $item->flag_progress,
            $tanggalPengumpulan, // <-- Tanggal yang sudah diformat
            $item->tahun_kegiatan ?? null,
            $item->master_kegiatan_id ?? null, // <-- [BONUS] Tampilkan ID masternya
        ];
    }

    /**
     * [PERBAIKAN 7] Sesuaikan 'headings()'
     */
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
            'Tahun Kegiatan',
            'ID Master Kegiatan' // <-- [BONUS] Heading untuk ID
        ];
    }

    /* * [PERBAIKAN 8] Perbaiki juga 'getDataForWord()' dan 'exportToWord()'
     * -----------------------------------------------------------------
     */

    public function exportToWord()
    {
        $templatePath = storage_path('templates/distribusi_triwulanan_template.docx');

        if (!file_exists($templatePath)) {
             return response()->json(['error' => 'Template Word tidak ditemukan.'], 404);
        }

        $templateProcessor = new TemplateProcessor($templatePath);
        $templateProcessor->setValue('tanggal_cetak', now()->format('d F Y'));

        // Logika judul laporan (ini sudah benar)
        $judulLaporan = 'Laporan Distribusi Triwulanan ' . strtoupper($this->jenisKegiatan);
        if (!empty($this->kegiatan)) {
             $judulLaporan .= ' - ' . (is_numeric($this->kegiatan) ? 'ID:'.$this->kegiatan : $this->kegiatan);
        }
        if (!empty($this->selectedTahun)) {
             $judulLaporan .= ' Tahun ' . $this->selectedTahun;
        }
        $templateProcessor->setValue('judul_laporan', $judulLaporan);

        $data = $this->getDataForWord(); // Panggil helper yang sudah benar
        $dataCount = $data->count();
        $placeholderToClone = 'id_distribusi'; // Pastikan ini ada di template docx-mu

        if ($dataCount > 0) {
            try {
                $templateProcessor->cloneRow($placeholderToClone, $dataCount);
            } catch (\Exception $e) {
                 return response()->json([
                    'error' => 'Gagal mengkloning baris di template. Pastikan placeholder `' . $placeholderToClone . '` ada.',
                    'details' => $e->getMessage()
                ], 500);
            }

            foreach ($data as $index => $row) {
                $i = $index + 1;
                
                // [FIX WORD] Gunakan logika nama yang sama
                $namaKegiatan = $row->masterKegiatan 
                                ? $row->masterKegiatan->nama_kegiatan 
                                : $row->nama_kegiatan;
                
                // [FIX WORD] Gunakan format tanggal yang konsisten
                $targetPenyelesaian = $row->target_penyelesaian ? $row->target_penyelesaian->format('d/m/Y') : '-';
                $tanggalPengumpulan = $row->tanggal_pengumpulan ? $row->tanggal_pengumpulan->format('d/m/Y') : '-';

                $templateProcessor->setValue('no#' . $i, $i);
                $templateProcessor->setValue('id_distribusi#' . $i, $row->id_distribusi_triwulanan ?? '');
                $templateProcessor->setValue('nama_kegiatan#' . $i, $namaKegiatan ?? ''); // <-- FIX
                $templateProcessor->setValue('blok_sensus#' . $i, $row->BS_Responden ?? '');
                $templateProcessor->setValue('pencacahan#' . $i, $row->pencacah ?? '');
                $templateProcessor->setValue('pengawas#' . $i, $row->pengawas ?? '');
                $templateProcessor->setValue('tanggal_target#' . $i, $targetPenyelesaian ?? ''); // <-- FIX
                $templateProcessor->setValue('flag_progress#' . $i, $row->flag_progress ?? '');
                $templateProcessor->setValue('tanggal_pengumpulan#' . $i, $tanggalPengumpulan ?? ''); // <-- FIX
                $templateProcessor->setValue('tahun_kegiatan#' . $i, $row->tahun_kegiatan ?? '');
            }
        } else {
            // Jika data kosong: tambahkan baris kosong
            // [INI BAGIAN YANG DIPERBAIKI]
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
                /* Abaikan jika gagal */ 
            }
        }

        // Sisa logika penyimpanan file (ini sudah benar)
        $fileName = 'DistribusiTriwulanan_' . strtoupper($this->jenisKegiatan);
        if (!empty($this->kegiatan)) {
             $fileName .= '_' . (is_numeric($this->kegiatan) ? 'ID'.$this->kegiatan : str_replace(' ', '_', $this->kegiatan));
        }
        $fileName .= '_' . $this->selectedTahun;
        $fileName .= '_' . date('Ymd_His') . '.docx';

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