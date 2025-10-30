<?php

namespace App\Imports;

use App\Models\Distribusi\DistribusiTriwulanan;
use App\Models\Master\MasterKegiatan;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsErrors;
use Carbon\Carbon;

class DistribusiTriwulananImport implements ToCollection, WithHeadingRow, SkipsOnError
{
    use SkipsErrors;

    protected $errors = [];
    protected $successCount = 0;

    protected $masterKegiatanMap;

    /**
     * Hanya muat data master KEGIATAN.
     */
    public function __construct()
    {
        // Buat map: ['Nama Kegiatan' => ID]
        $this->masterKegiatanMap = MasterKegiatan::pluck('id_master_kegiatan', 'nama_kegiatan');
        // $this->masterPetugasMap = MasterPetugas::pluck('nama_petugas', 'nama_petugas'); // <-- [DIHAPUS]
    }

    public function collection(Collection $rows)
    {
        foreach ($rows as $index => $row) {
            $rowNumber = $index + 2;

            try {
                $rowArray = $row->toArray();
                
                // Validasi data (sekarang lebih sederhana)
                $validation = $this->validateRow($rowArray, $rowNumber);

                if (!$validation['valid']) {
                    $this->errors[] = [
                        'row' => $rowNumber,
                        'error' => $validation['message'],
                        'values' => $rowArray['nama_kegiatan'] ?? $rowArray['flag_progress'] ?? 'N/A' // Sesuaikan field yang relevan
                    ];
                    continue;
                }

                // Data sudah tervalidasi (sebagian), siap di-create
                DistribusiTriwulanan::create($validation['data']);

                $this->successCount++;

            } catch (\Exception $e) {
                $this->errors[] = [
                    'row' => $rowNumber,
                    'error' => $e->getMessage(),
                    'values' => $rowArray['nama_kegiatan'] ?? 'N/A'
                ];
            }
        }
    }

    /**
     * Validasi disederhanakan: Hanya Kegiatan dan Flag Progress.
     */
    protected function validateRow($row, $rowNumber)
    {
        $dataToCreate = []; // Kumpulkan data bersih di sini

        // 1. Validasi Kolom Wajib (tetap penting)
        $requiredFields = [
            'nama_kegiatan'     => 'Nama Kegiatan',
            'bs_responden'      => 'BS Responden',
            'pencacah'          => 'Pencacah',          // Tetap wajib diisi di Excel
            'pengawas'          => 'Pengawas',          // Tetap wajib diisi di Excel
            'target_penyelesaian' => 'Target Penyelesaian',
            'flag_progress'     => 'Flag Progress',
        ];

        foreach ($requiredFields as $field => $label) {
            // Gunakan isset dan cek null/string kosong yang lebih robust
            if (!isset($row[$field]) || $row[$field] === null || trim((string)$row[$field]) === '') {
                return ['valid' => false, 'message' => "{$label} tidak boleh kosong"];
            }
        }
        
        // 2. Validasi Nama Kegiatan (WAJIB ke Master)
        $namaKegiatan = trim($row['nama_kegiatan']);
        if (!isset($this->masterKegiatanMap[$namaKegiatan])) {
            return ['valid' => false, 'message' => "Nama Kegiatan '{$namaKegiatan}' tidak terdaftar di master."];
        }
        $dataToCreate['master_kegiatan_id'] = $this->masterKegiatanMap[$namaKegiatan]; // Ambil ID!
        $dataToCreate['nama_kegiatan'] = $namaKegiatan; // Tetap simpan namanya

        // 3. Ambil Data Pencacah (TANPA VALIDASI ke Master)
        $dataToCreate['pencacah'] = trim($row['pencacah']);
        // Blok validasi pencacah dihapus

        // 4. Ambil Data Pengawas (TANPA VALIDASI ke Master)
        $dataToCreate['pengawas'] = trim($row['pengawas']);
        // Blok validasi pengawas dihapus

        // 5. Validasi Flag Progress (WAJIB)
        $flagProgressInput = strtolower(trim($row['flag_progress']));
        if (in_array($flagProgressInput, ['selesai', 'done', '1'])) {
             $dataToCreate['flag_progress'] = 'Selesai';
        } elseif (in_array($flagProgressInput, ['belum', 'belum selesai', 'progress', '0'])) {
             $dataToCreate['flag_progress'] = 'Belum Selesai';
        } else {
            return ['valid' => false, 'message' => "Flag Progress '{$row['flag_progress']}' tidak valid (gunakan: Selesai/Belum Selesai)"];
        }

        // 6. Validasi Tanggal (tetap penting)
        try {
            $dataToCreate['target_penyelesaian'] = $this->parseDate($row['target_penyelesaian'], false); // Wajib isi
            $dataToCreate['tanggal_pengumpulan'] = $this->parseDate($row['tanggal_pengumpulan'], true); // Boleh null
        } catch (\Exception $e) {
            return ['valid' => false, 'message' => $e->getMessage()];
        }

        // 7. Masukkan sisa data
        $dataToCreate['BS_Responden'] = $row['bs_responden'];
        // Pastikan target_penyelesaian sudah di-parse sebelum menghitung tahun
        if (isset($dataToCreate['target_penyelesaian'])) {
            $dataToCreate['tahun_kegiatan'] = Carbon::parse($dataToCreate['target_penyelesaian'])->year;
        } else {
            // Handle jika target_penyelesaian gagal di-parse (seharusnya tidak terjadi krn validasi)
             return ['valid' => false, 'message' => "Target Penyelesaian tidak valid untuk menghitung tahun"];
        }


        // Semua validasi lolos, kembalikan data bersih
        return ['valid' => true, 'data' => $dataToCreate];
    }

/**
 * Fungsi parseDate (Tidak berubah, sudah benar)
 */
protected function parseDate($date, $isNullable = false)
    {
        // Hapus spasi di awal/akhir string
        $date = is_string($date) ? trim($date) : $date;

        // Cek jika kosong
        if (empty($date)) {
            if ($isNullable) return null; // Jika boleh null, kembalikan null
            // Jika tidak boleh null, lempar error
            throw new \Exception("Tanggal wajib diisi dan tidak boleh kosong");
        }
        
        // Coba parse jika formatnya angka (Excel date serial number)
        if (is_numeric($date)) {
            // [INI BARIS YANG DIPERBAIKI - TIDAK ADA SPASI DI AWAL]
            return Carbon::instance(\PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($date))
                   ->format('Y-m-d H:i:s'); // Format sebagai datetime
        }

        // Coba parse dengan format umum
        $formats = ['Y-m-d', 'd/m/Y', 'd-m-Y', 'Y/m/d', 'Y-m-d H:i:s'];
        foreach ($formats as $format) {
            try {
                // Parsing ketat (createFromFormat)
                return Carbon::createFromFormat($format, (string)$date)->format('Y-m-d H:i:s');
            } catch (\Exception $e) {
                // Abaikan error jika format tidak cocok, coba format berikutnya
            }
        }

        // Jika format di atas gagal, coba parsing otomatis (kurang ketat)
        try {
            return Carbon::parse((string)$date)->format('Y-m-d H:i:s');
        } catch (\Exception $e) {
            // Jika semua gagal, lempar error
        }
        
        // Pesan error jika semua format gagal
        throw new \Exception("Format tanggal tidak valid: '{$date}' (gunakan YYYY-MM-DD atau DD/MM/YYYY)");
    }

    public function getErrors()
    {
        return $this->errors;
    }

    public function getSuccessCount()
    {
        return $this->successCount;
    }
}