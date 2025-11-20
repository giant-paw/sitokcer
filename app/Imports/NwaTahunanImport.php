<?php

namespace App\Imports;

use App\Models\Nwa\NwaTahunan;
use App\Models\Master\MasterKegiatan;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsErrors;
use Carbon\Carbon;

class NwaTahunanImport implements ToCollection, WithHeadingRow, SkipsOnError
{
    use SkipsErrors;

    protected $errors = [];
    protected $successCount = 0;
    protected $masterKegiatanMap;

    public function __construct()
    {
        $this->masterKegiatanMap = MasterKegiatan::where('modul', 'nwa_tahunan')
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
                
                NwaTahunan::create($validation['data']);
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
        ];

        foreach ($requiredFields as $field => $label) {
            if (!isset($row[$field]) || $row[$field] === null || trim((string)$row[$field]) === '') {
                return ['valid' => false, 'message' => "Kolom '{$label}' tidak boleh kosong"];
            }
        }
        
        $namaKegiatan = trim($row['nama_kegiatan']);
        if (!isset($this->masterKegiatanMap[$namaKegiatan])) {
            return ['valid' => false, 'message' => "Nama Kegiatan '{$namaKegiatan}' tidak terdaftar di master (modul nwa_tahunan)."];
        }
        $dataToCreate['master_kegiatan_id'] = $this->masterKegiatanMap[$namaKegiatan]; 
        $dataToCreate['nama_kegiatan'] = $namaKegiatan; 

        $dataToCreate['pencacah'] = trim($row['pencacah']);
        $dataToCreate['pengawas'] = trim($row['pengawas']);

        $flagProgressInput = strtolower(trim($row['flag_progress'] ?? '')); 
        if (in_array($flagProgressInput, ['selesai', 'done', '1'])) {
            $dataToCreate['flag_progress'] = 'Selesai';
        } else {
            $dataToCreate['flag_progress'] = 'Belum Selesai';
        }

        try {
            $dataToCreate['target_penyelesaian'] = $this->parseDate($row['target_penyelesaian'], false); 
            $dataToCreate['tanggal_pengumpulan'] = $this->parseDate($row['tanggal_pengumpulan'], true); 
        } catch (\Exception $e) {
            return ['valid' => false, 'message' => $e->getMessage()];
        }

        $dataToCreate['BS_Responden'] = $row['bs_responden'];

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
        $formats = ['Y-m-d', 'd/m/Y', 'd-m-Y', 'Y/m/d', 'Y-m-d H:i:s', 'Y-n-j G:i:s']; 
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