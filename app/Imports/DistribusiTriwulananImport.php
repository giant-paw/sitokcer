<?php

namespace App\Imports;

use App\Models\Distribusi\DistribusiTriwulanan;
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
                        'error' => $validation['message']
                    ];
                    continue;
                }

                $targetPenyelesaian = $this->parseDate($rowArray['target_penyelesaian']);
                $tanggalPengumpulan = $this->parseDate($rowArray['tanggal_pengumpulan']);

                DistribusiTriwulanan::create([
                    'nama_kegiatan'       => $rowArray['nama_kegiatan'],
                    'BS_Responden'        => $rowArray['bs_responden'],
                    'pencacah'            => $rowArray['pencacah'],
                    'pengawas'            => $rowArray['pengawas'],
                    'target_penyelesaian' => $targetPenyelesaian,
                    'flag_progress'       => strtoupper($rowArray['flag_progress']),
                    'tanggal_pengumpulan' => $tanggalPengumpulan,
                ]);

                $this->successCount++;
            } catch (\Exception $e) {
                $this->errors[] = [
                    'row' => $rowNumber,
                    'error' => $e->getMessage()
                ];
            }
        }
    }

    protected function validateRow($row, $rowNumber)
    {
        $requiredFields = [
            'nama_kegiatan'        => 'Nama Kegiatan',
            'bs_responden'         => 'BS Responden',
            'pencacah'             => 'Pencacah',
            'pengawas'             => 'Pengawas',
            'target_penyelesaian'  => 'Target Penyelesaian',
            'flag_progress'        => 'Flag Progress',
            'tanggal_pengumpulan'  => 'Tanggal Pengumpulan',
        ];

        foreach ($requiredFields as $field => $label) {
            if (empty($row[$field])) {
                return [
                    'valid' => false,
                    'message' => "{$label} tidak boleh kosong"
                ];
            }
        }

        // Validasi nama_kegiatan, pencacah, pengawas harus huruf
        foreach (['nama_kegiatan', 'pencacah', 'pengawas'] as $col) {
            if (!preg_match('/[a-zA-Z]/', $row[$col] ?? '')) {
                return [
                    'valid' => false,
                    'message' => ucfirst($col) . ' harus mengandung huruf'
                ];
            }
        }

        // Flag progress harus BELUM/SELESAI
        $validFlags = ['BELUM', 'SELESAI'];
        if (!in_array(strtoupper($row['flag_progress']), $validFlags)) {
            return [
                'valid' => false,
                'message' => "Flag Progress hanya boleh: BELUM atau SELESAI"
            ];
        }

        try {
            $this->parseDate($row['target_penyelesaian']);
            $this->parseDate($row['tanggal_pengumpulan']);
        } catch (\Exception $e) {
            return [
                'valid' => false,
                'message' => $e->getMessage()
            ];
        }
        return ['valid' => true];
    }

    protected function parseDate($date)
    {
        if ($date === null) throw new \Exception("Tanggal wajib diisi dan tidak boleh kosong");
        if (is_numeric($date)) {
            return Carbon::instance(\PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($date))
                ->format('Y-m-d');
        }
        $formats = ['Y-m-d', 'd/m/Y', 'd-m-Y', 'Y/m/d'];
        foreach ($formats as $format) {
            try {
                return Carbon::createFromFormat($format, (string)$date)->format('Y-m-d');
            } catch (\Exception $e) {
            }
        }
        try {
            return Carbon::parse((string)$date)->format('Y-m-d');
        } catch (\Exception $e) {
        }
        throw new \Exception("Format tanggal tidak valid: $date (gunakan YYYY-MM-DD atau DD/MM/YYYY)");
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
