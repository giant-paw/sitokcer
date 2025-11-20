<?php

namespace App\Imports;

use App\Models\Sosial\SosialTahunan;
use App\Models\Master\MasterKegiatan;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsErrors;
use Carbon\Carbon;
use Illuminate\Database\QueryException; // Tambahkan ini

class SosialTahunanImport implements ToCollection, WithHeadingRow, SkipsOnError
{
    use SkipsErrors;

    protected $errors = [];
    protected $successCount = 0;
    protected $masterKegiatanMap;
    protected $currentModul;

    public function __construct($currentModul)
    {
        $this->currentModul = $currentModul;
        // Mapping Nama Kegiatan -> ID
        $this->masterKegiatanMap = MasterKegiatan::where('modul', $this->currentModul)
            ->pluck('id_master_kegiatan', 'nama_kegiatan');
    }

    public function collection(Collection $rows)
    {
        foreach ($rows as $index => $row) {
            $rowNumber = $index + 2; 

            try {
                $rowArray = $row->toArray();
                
                // 1. Validasi Logic (PHP)
                $validation = $this->validateRow($rowArray, $rowNumber);

                if (!$validation['valid']) {
                    $this->errors[] = [
                        'row'    => $rowNumber,
                        'error'  => $validation['message'],
                        'values' => $rowArray['nama_kegiatan'] ?? 'N/A'
                    ];
                    continue;
                }

                // 2. Eksekusi Insert ke Database
                SosialTahunan::create($validation['data']);
                $this->successCount++;

            } catch (QueryException $e) {
                // --- PENANGANAN ERROR DATABASE RAPI ---
                $errorCode = $e->errorInfo[1] ?? 0;
                $errorMessage = $e->getMessage();
                $friendlyError = "Gagal menyimpan ke Database.";

                // Error 1048: Column cannot be null
                if ($errorCode == 1048) {
                    if (str_contains($errorMessage, 'tanggal_pengumpulan')) {
                        $friendlyError = "Kolom 'Tanggal Pengumpulan' wajib diisi (Database menolak nilai kosong).";
                    } elseif (str_contains($errorMessage, 'master_kegiatan_id')) {
                        $friendlyError = "Kegiatan tidak valid atau ID Kegiatan kosong.";
                    } else {
                        // Ambil nama kolom dari pesan error
                        preg_match("/Column '(.+?)' cannot be null/", $errorMessage, $matches);
                        $colName = $matches[1] ?? 'Wajib';
                        $friendlyError = "Kolom '{$colName}' tidak boleh kosong.";
                    }
                } 
                // Error 1452: Foreign Key fails
                elseif ($errorCode == 1452) {
                    $friendlyError = "Data referensi (Kegiatan/Petugas) tidak ditemukan di Master.";
                }

                $this->errors[] = [
                    'row'    => $rowNumber,
                    'error'  => $friendlyError . " (System: {$errorMessage})", // Tampilkan pesan user + detail
                    'values' => $rowArray['nama_kegiatan'] ?? 'N/A'
                ];

            } catch (\Exception $e) {
                // Error Umum Lainnya
                $this->errors[] = [
                    'row'    => $rowNumber,
                    'error'  => "Terjadi kesalahan sistem: " . $e->getMessage(),
                    'values' => $rowArray['nama_kegiatan'] ?? 'N/A'
                ];
            }
        }
    }

    protected function validateRow($row, $rowNumber)
    {
        $dataToCreate = [];

        // 1. Validasi Kolom Wajib Dasar (Cek Kekosongan String)
        $requiredFields = [
            'nama_kegiatan'       => 'Nama Kegiatan',
            'bs_responden'        => 'BS Responden',
            'pencacah'            => 'Pencacah',
            'pengawas'            => 'Pengawas',
            'target_penyelesaian' => 'Target Penyelesaian',
            'flag_progress'       => 'Flag Progress',
            // 'tanggal_pengumpulan' tidak ditaruh sini agar ditangani logic database/parseDate
        ];

        foreach ($requiredFields as $field => $label) {
            if (!isset($row[$field]) || trim((string)$row[$field]) === '') {
                return ['valid' => false, 'message' => "Kolom '{$label}' tidak boleh kosong."];
            }
        }
        
        // 2. Validasi Master Kegiatan
        $namaKegiatan = trim($row['nama_kegiatan']);
        if (!isset($this->masterKegiatanMap[$namaKegiatan])) {
            return ['valid' => false, 'message' => "Kegiatan '{$namaKegiatan}' tidak terdaftar di Master Modul {$this->currentModul}."];
        }
        $dataToCreate['master_kegiatan_id'] = $this->masterKegiatanMap[$namaKegiatan];
        $dataToCreate['nama_kegiatan'] = $namaKegiatan;

        // 3. Data String
        $dataToCreate['pencacah']     = trim($row['pencacah']);
        $dataToCreate['pengawas']     = trim($row['pengawas']);
        $dataToCreate['BS_Responden'] = trim($row['bs_responden']);

        // 4. Validasi Flag Progress
        $flagInput = strtolower(trim($row['flag_progress']));
        if (in_array($flagInput, ['selesai', 'done', '1'])) {
             $dataToCreate['flag_progress'] = 'Selesai';
        } elseif (in_array($flagInput, ['belum', 'belum selesai', 'progress', '0'])) {
             $dataToCreate['flag_progress'] = 'Belum Selesai';
        } else {
            return ['valid' => false, 'message' => "Flag Progress '{$row['flag_progress']}' tidak valid. Gunakan: Selesai / Belum Selesai."];
        }

        // 5. Validasi Tanggal
        try {
            $dataToCreate['target_penyelesaian'] = $this->parseDate($row['target_penyelesaian'], false);
            
            // PENTING: Jika database Anda menolak NULL, tapi user mengosongkan Excel,
            // QueryException (di atas) akan menangkapnya.
            $dataToCreate['tanggal_pengumpulan'] = $this->parseDate($row['tanggal_pengumpulan'], true); 
            
            $dataToCreate['tahun_kegiatan'] = Carbon::parse($dataToCreate['target_penyelesaian'])->year;
        } catch (\Exception $e) {
            return ['valid' => false, 'message' => $e->getMessage()];
        }

        return ['valid' => true, 'data' => $dataToCreate];
    }

    // ... (Fungsi parseDate, getErrors, getSuccessCount SAMA SEPERTI SEBELUMNYA)
    protected function parseDate($date, $isNullable = false)
    {
        $date = is_string($date) ? trim($date) : $date;

        if (empty($date)) {
            if ($isNullable) return null;
            throw new \Exception("Tanggal wajib diisi.");
        }
        
        if (is_numeric($date) && $date > 25569) {
            try {
                return Carbon::instance(\PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($date))
                        ->format('Y-m-d H:i:s');
            } catch (\Exception $e) {}
        }

        $formats = ['Y-m-d', 'd/m/Y', 'd-m-Y', 'Y/m/d'];
        foreach ($formats as $format) {
            try {
                return Carbon::createFromFormat($format, (string)$date)->format('Y-m-d H:i:s');
            } catch (\Exception $e) {}
        }
        try {
            return Carbon::parse((string)$date)->format('Y-m-d H:i:s');
        } catch (\Exception $e) {}
        
        throw new \Exception("Format tanggal '{$date}' salah (Gunakan YYYY-MM-DD).");
    }

    public function getErrors() { return $this->errors; }
    public function getSuccessCount() { return $this->successCount; }
}