<?php

namespace App\Imports;

use App\Models\Produksi\ProduksiCaturwulanan;
use App\Models\Master\MasterKegiatan;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsErrors;
use Carbon\Carbon;

class ProduksiCaturwulananImport implements ToCollection, WithHeadingRow, SkipsOnError
{
    use SkipsErrors;

    protected $errors = [];
    protected $successCount = 0;

    protected $masterKegiatanMap;

    /**
     * Hanya muat data master KEGIATAN yang relevan dengan modul ini.
     */
    public function __construct()
    {
        // Buat map: ['Nama Kegiatan' => ID]
        // Filter hanya kegiatan yang termasuk modul ini
        $this->masterKegiatanMap = MasterKegiatan::where('modul', 'produksi_caturwulanan')
                                                ->pluck('id_master_kegiatan', 'nama_kegiatan');
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
                        'values' => $rowArray['nama_kegiatan'] ?? 'N/A'
                    ];
                    continue;
                }

                // Data sudah tervalidasi (sebagian), siap di-create
                ProduksiCaturwulanan::create($validation['data']);

                $this->successCount++;

            } catch (\Exception $e) {
                // Tangkap error jika ada duplikasi data unik atau error DB lainnya
                $this->errors[] = [
                    'row' => $rowNumber,
                    'error' => $e->getMessage(),
                    'values' => $rowArray['nama_kegiatan'] ?? 'N/A'
                ];
            }
        }
    }

    /**
     * Validasi disederhanakan: Hanya Nama Kegiatan dan Tanggal.
     */
    protected function validateRow($row, $rowNumber)
    {
        $dataToCreate = []; // Kumpulkan data bersih di sini

        // 1. Validasi Kolom Wajib
        $requiredFields = [
            'nama_kegiatan'     => 'Nama Kegiatan',
            'bs_responden'      => 'BS Responden',
            'pencacah'          => 'Pencacah',
            'pengawas'          => 'Pengawas',
            'target_penyelesaian' => 'Target Penyelesaian',
            // 'flag_progress' tidak wajib lagi
        ];

        foreach ($requiredFields as $field => $label) {
            if (!isset($row[$field]) || $row[$field] === null || trim((string)$row[$field]) === '') {
                return ['valid' => false, 'message' => "{$label} tidak boleh kosong"];
            }
        }
        
        // 2. Validasi Nama Kegiatan (WAJIB ke Master)
        $namaKegiatan = trim($row['nama_kegiatan']);
        if (!isset($this->masterKegiatanMap[$namaKegiatan])) {
            return ['valid' => false, 'message' => "Nama Kegiatan '{$namaKegiatan}' tidak terdaftar di master (modul produksi_caturwulanan)."];
        }
        $dataToCreate['master_kegiatan_id'] = $this->masterKegiatanMap[$namaKegiatan]; // Ambil ID!
        $dataToCreate['nama_kegiatan'] = $namaKegiatan; // Tetap simpan namanya

        // 3. Ambil Data Pencacah (TANPA VALIDASI ke Master)
        $dataToCreate['pencacah'] = trim($row['pencacah']);

        // 4. Ambil Data Pengawas (TANPA VALIDASI ke Master)
        $dataToCreate['pengawas'] = trim($row['pengawas']);

        // 5. Validasi Flag Progress (FLEKSIBEL, TIDAK WAJIB)
        $flagProgressInput = strtolower(trim($row['flag_progress'] ?? '')); // Ambil atau string kosong
        if (in_array($flagProgressInput, ['selesai', 'done', '1'])) {
            $dataToCreate['flag_progress'] = 'Selesai';
        } else {
            // Jika kosong, salah, atau 'belum', default ke 'Belum Selesai'
            $dataToCreate['flag_progress'] = 'Belum Selesai';
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
        if (isset($dataToCreate['target_penyelesaian'])) {
             // $dataToCreate['tahun_kegiatan'] = Carbon::parse($dataToCreate['target_penyelesaian'])->year;
        } else {
             return ['valid' => false, 'message' => "Target Penyelesaian tidak valid untuk menghitung tahun"];
        }

        return ['valid' => true, 'data' => $dataToCreate];
    }

    protected function parseDate($date, $isNullable = false)
    {
        $date = is_string($date) ? trim($date) : $date;
        if (empty($date)) {
            if ($isNullable) return null; 
            throw new \Exception("Tanggal wajib diisi dan tidak boleh kosong");
        }
        if (is_numeric($date)) {
            return Carbon::instance(\PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($date))
                ->format('Y-m-d H:i:s');
        }
        $formats = ['Y-m-d', 'd/m/Y', 'd-m-Y', 'Y/m/d', 'Y-m-d H:i:s'];
        foreach ($formats as $format) {
            try {
                return Carbon::createFromFormat($format, (string)$date)->format('Y-m-d H:i:s');
            } catch (\Exception $e) { /* Coba format berikutnya */ }
        }
        try {
            return Carbon::parse((string)$date)->format('Y-m-d H:i:s');
        } catch (\Exception $e) { /* Gagal */ }
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