<?php

namespace App\Imports;
use App\Models\Distribusi\DistribusiBulanan;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsErrors;
use Illuminate\Support\Collection;
use Carbon\Carbon;

class DistribusiBulananImport implements ToCollection, WithHeadingRow, SkipsOnError
{
    use SkipsErrors;

    protected $errors = [];
    protected $successCount = 0;
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
                        'values' => $rowArray['nama_kegiatan'] ?? $rowArray['flag_progress'] ?? 'N/A'
                    ];
                    continue;
                }

                DistribusiBulanan::create($validation['data']); // <-- [GANTI MODEL]
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

    protected function validateRow($row, $rowNumber)
    {
        $dataToCreate = []; 

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
                return ['valid' => false, 'message' => "{$label} tidak boleh kosong"];
            }
        }
        
        // 1. Validasi Nama Kegiatan (WAJIB ke Master)
        $namaKegiatan = trim($row['nama_kegiatan']);
        // Cek ke Peta (Map) yang sudah difilter by modul
        if (!isset($this->masterKegiatanMap[$namaKegiatan])) {
            return ['valid' => false, 'message' => "Nama Kegiatan '{$namaKegiatan}' tidak terdaftar di master untuk modul {$this->currentModul}."];
        }
        $dataToCreate['master_kegiatan_id'] = $this->masterKegiatanMap[$namaKegiatan];
        $dataToCreate['nama_kegiatan'] = $namaKegiatan; 

        // 2. Ambil Data Pencacah & Pengawas (TANPA VALIDASI ke Master)
        $dataToCreate['pencacah'] = trim($row['pencacah']);
        $dataToCreate['pengawas'] = trim($row['pengawas']);

        // 3. Validasi Flag Progress (WAJIB)
        $flagProgressInput = strtolower(trim($row['flag_progress']));
        if (in_array($flagProgressInput, ['selesai', 'done', '1'])) {
             $dataToCreate['flag_progress'] = 'Selesai';
        } elseif (in_array($flagProgressInput, ['belum', 'belum selesai', 'progress', '0'])) {
             $dataToCreate['flag_progress'] = 'Belum Selesai';
        } else {
            return ['valid' => false, 'message' => "Flag Progress '{$row['flag_progress']}' tidak valid (gunakan: Selesai/Belum Selesai)"];
        }

        // 4. Validasi Tanggal
        try {
            $dataToCreate['target_penyelesaian'] = $this->parseDate($row['target_penyelesaian'], false);
            // 'tanggal_pengumpulan' dibuat opsional (boleh null)
            $dataToCreate['tanggal_pengumpulan'] = $this->parseDate($row['tanggal_pengumpulan'], true); 
        } catch (\Exception $e) {
            return ['valid' => false, 'message' => $e->getMessage()];
        }

        // 5. Masukkan sisa data
        $dataToCreate['BS_Responden'] = $row['bs_responden'];
        if (isset($dataToCreate['target_penyelesaian'])) {
            $dataToCreate['tahun_kegiatan'] = Carbon::parse($dataToCreate['target_penyelesaian'])->year;
        } else {
             return ['valid' => false, 'message' => "Target Penyelesaian tidak valid"];
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
            } catch (\Exception $e) {}
        }
        try {
            return Carbon::parse((string)$date)->format('Y-m-d H:i:s');
        } catch (\Exception $e) {}
        throw new \Exception("Format tanggal tidak valid: '{$date}'");
    }

    public function getErrors() { return $this->errors; }
    public function getSuccessCount() { return $this->successCount; }
}