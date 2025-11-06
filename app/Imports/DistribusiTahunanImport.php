<?php

namespace App\Imports;

use App\Models\Distribusi\DistribusiTahunan; 
use App\Models\Master\MasterKegiatan;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsErrors;
use Carbon\Carbon;

class DistribusiTahunanImport implements ToCollection, WithHeadingRow, SkipsOnError
{
    use SkipsErrors;

    protected $errors = [];
    protected $successCount = 0;
    protected $masterKegiatanMap;
    protected $currentModul; 

    public function __construct($currentModul)
    {
        $this->currentModul = $currentModul;
        $this->masterKegiatanMap = MasterKegiatan::where('modul', $this->currentModul)
                                               ->pluck('id_master_kegiatan', 'nama_kegiatan');
    }

    public function collection(Collection $rows)
    {
        foreach ($rows as $index => $row) {
            $rowNumber = $index + 2; 

            try {
                $rowArray = $row->toArray();
                $validation = $this->validateRow($rowArray, $rowNumber);

                if (!$validation['valid']) {
                    $this->errors[] = [
                        'row' => $rowNumber,
                        'error' => $validation['message'],
                        'values' => $rowArray['nama_kegiatan'] ?? 'N/A'
                    ];
                    continue;
                }

                DistribusiTahunan::create($validation['data']); // <-- Model Tahunan
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
     * Validasi disederhanakan: Hanya Kegiatan.
     */
    protected function validateRow($row, $rowNumber)
    {
        $dataToCreate = []; // Kumpulkan data bersih di sini

        // 1. Validasi Kolom Wajib (minimal)
        $requiredFields = [
            'nama_kegiatan'     => 'Nama Kegiatan',
            'bs_responden'      => 'BS Responden',
            'pencacah'          => 'Pencacah',
            'pengawas'          => 'Pengawas',
            'target_penyelesaian' => 'Target Penyelesaian',
            'flag_progress'     => 'Flag Progress',
        ];

        foreach ($requiredFields as $field => $label) {
            if (!isset($row[$field]) || $row[$field] === null || trim((string)$row[$field]) === '') {
                return ['valid' => false, 'message' => "Kolom '{$label}' tidak boleh kosong"];
            }
        }
        
        // 2. Validasi Nama Kegiatan (WAJIB ke Master)
        $namaKegiatan = trim($row['nama_kegiatan']);
        // Cek ke Peta (Map) yang sudah difilter by modul
        if (!isset($this->masterKegiatanMap[$namaKegiatan])) {
            return ['valid' => false, 'message' => "Nama Kegiatan '{$namaKegiatan}' tidak terdaftar di master untuk modul {$this->currentModul}."];
        }
        $dataToCreate['master_kegiatan_id'] = $this->masterKegiatanMap[$namaKegiatan]; // Ambil ID!
        $dataToCreate['nama_kegiatan'] = $namaKegiatan; // Tetap simpan namanya

        // 3. Ambil Data Lain (TANPA VALIDASI)
        $dataToCreate['pencacah'] = trim($row['pencacah']);
        $dataToCreate['pengawas'] = trim($row['pengawas']);
        $dataToCreate['flag_progress'] = trim($row['flag_progress']); // <-- Diambil apa adanya
        $dataToCreate['BS_Responden'] = $row['bs_responden'];

        // 4. Validasi Tanggal (Tetap diperlukan agar tidak error)
        try {
            $dataToCreate['target_penyelesaian'] = $this->parseDate($row['target_penyelesaian'], false);
            $dataToCreate['tanggal_pengumpulan'] = $this->parseDate($row['tanggal_pengumpulan'], true); // Boleh null
        } catch (\Exception $e) {
            return ['valid' => false, 'message' => $e->getMessage()];
        }

        // 5. Masukkan sisa data
        if (isset($dataToCreate['target_penyelesaian'])) {
            $dataToCreate['tahun_kegiatan'] = Carbon::parse($dataToCreate['target_penyelesaian'])->year;
        } else {
             return ['valid' => false, 'message' => "Target Penyelesaian tidak valid"];
        }

        // Semua validasi lolos, kembalikan data bersih
        return ['valid' => true, 'data' => $dataToCreate];
    }

    /**
     * Fungsi parseDate (Tidak berubah)
     */
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
            } catch (\Exception $e) {}
        }
        try {
            return Carbon::parse((string)$date)->format('Y-m-d H:i:s');
        } catch (\Exception $e) {}
        
        throw new \Exception("Format tanggal tidak valid: '{$date}' (gunakan YYYY-MM-DD atau DD/MM/YYYY)");
    }

    /**
     * Mengembalikan array error yang dikumpulkan.
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * Mengembalikan jumlah baris yang sukses.
     */
    public function getSuccessCount()
    {
        return $this->successCount;
    }
}