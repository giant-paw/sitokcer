<?php

namespace App\Imports;

use App\Models\Sosial\SosialTriwulanan;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsErrors;
use Illuminate\Support\Collection;
use Carbon\Carbon;

class SosialTriwulananImport implements ToCollection, WithHeadingRow, SkipsOnError
{
    use SkipsErrors;

    protected $errors = [];
    protected $successCount = 0;

    public function collection(Collection $rows)
    {
        foreach ($rows as $index => $row) {
            $rowNumber = $index + 2;

            try {
                // Convert Collection ke array
                $rowArray = $row->toArray();

                // VALIDASI WAJIB ISI SEMUA KOLOM + FORMAT
                $validation = $this->validateRow($rowArray, $rowNumber);
                if (!$validation['valid']) {
                    $this->errors[] = [
                        'row' => $rowNumber,
                        'error' => $validation['message']
                    ];
                    continue;
                }

                // Parse tanggal (keduanya WAJIB)
                $targetPenyelesaian = $this->parseDate($this->val($rowArray, 'target_penyelesaian'));
                $tanggalPengumpulan = $this->parseDate($this->val($rowArray, 'tanggal_pengumpulan'));

                // Insert data
                SosialTriwulanan::create([
                    'nama_kegiatan'       => $this->val($rowArray, 'nama_kegiatan'),
                    'BS_Responden'        => $this->val($rowArray, 'bs_responden'),
                    'pencacah'            => $this->val($rowArray, 'pencacah'),
                    'pengawas'            => $this->val($rowArray, 'pengawas'),
                    'target_penyelesaian' => $targetPenyelesaian,
                    'flag_progress'       => strtoupper($this->val($rowArray, 'flag_progress')),
                    'tanggal_pengumpulan' => $tanggalPengumpulan,
                ]);

                $this->successCount++;
            } catch (\Exception $e) {
                $this->errors[] = [
                    'row' => $rowNumber,
                    'error' => 'Baris ' . $rowNumber . ': ' . $e->getMessage()
                ];
            }
        }
    }

    // Helper: ambil nilai + trim spasi
    protected function val($row, $key)
    {
        if (!array_key_exists($key, $row)) {
            return null;
        }

        $v = $row[$key];

        if (is_string($v)) {
            $v = trim($v);
        }

        return $v === '' ? null : $v;
    }

    protected function validateRow($row, $rowNumber)
    {
        // 1. CEK SEMUA FIELD WAJIB DIISI
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
            if ($this->val($row, $field) === null) {
                return [
                    'valid' => false,
                    'message' => "Baris {$rowNumber}: {$label} tidak boleh kosong"
                ];
            }
        }

        // 2. VALIDASI NAMA KEGIATAN (tidak boleh pure angka)
        if (!$this->isValidText($this->val($row, 'nama_kegiatan'))) {
            return [
                'valid' => false,
                'message' => "Baris {$rowNumber}: Nama Kegiatan harus berisi huruf, tidak boleh hanya angka"
            ];
        }

        // 3. VALIDASI PENCACAH (tidak boleh pure angka)
        if (!$this->isValidText($this->val($row, 'pencacah'))) {
            return [
                'valid' => false,
                'message' => "Baris {$rowNumber}: Pencacah harus berisi huruf, tidak boleh hanya angka"
            ];
        }

        // 4. VALIDASI PENGAWAS (tidak boleh pure angka)
        if (!$this->isValidText($this->val($row, 'pengawas'))) {
            return [
                'valid' => false,
                'message' => "Baris {$rowNumber}: Pengawas harus berisi huruf, tidak boleh hanya angka"
            ];
        }

        // 5. VALIDASI FLAG PROGRESS (HANYA BELUM ATAU SELESAI)
        $validFlags = ['BELUM', 'SELESAI'];
        $flagValue = strtoupper($this->val($row, 'flag_progress'));

        if (!in_array($flagValue, $validFlags)) {
            return [
                'valid' => false,
                'message' => "Baris {$rowNumber}: Flag Progress hanya boleh: BELUM atau SELESAI"
            ];
        }

        // 6. VALIDASI FORMAT TANGGAL
        try {
            $this->parseDate($this->val($row, 'target_penyelesaian'));
            $this->parseDate($this->val($row, 'tanggal_pengumpulan'));
        } catch (\Exception $e) {
            return [
                'valid' => false,
                'message' => "Baris {$rowNumber}: " . $e->getMessage()
            ];
        }

        return ['valid' => true];
    }

    /**
     * Validasi text: harus mengandung huruf, tidak boleh pure angka
     */
    protected function isValidText($value)
    {
        if ($value === null || $value === '') {
            return false;
        }

        // Cek apakah pure numeric (angka saja)
        if (is_numeric($value)) {
            return false;
        }

        // Cek apakah mengandung minimal 1 huruf
        if (!preg_match('/[a-zA-Z]/', $value)) {
            return false;
        }

        return true;
    }

    protected function parseDate($date)
    {
        if ($date === null) {
            throw new \Exception("Tanggal wajib diisi dan tidak boleh kosong");
        }

        // Excel serial number
        if (is_numeric($date)) {
            return Carbon::instance(\PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($date))
                ->format('Y-m-d H:i:s');
        }

        $formats = ['Y-m-d', 'd/m/Y', 'd-m-Y', 'Y/m/d', 'Y-m-d H:i:s'];
        foreach ($formats as $format) {
            try {
                return Carbon::createFromFormat($format, (string)$date)->format('Y-m-d H:i:s');
            } catch (\Exception $e) {
                // coba format lain
            }
        }

        // Fallback parse bebas
        try {
            return Carbon::parse((string)$date)->format('Y-m-d H:i:s');
        } catch (\Exception $e) {
            throw new \Exception("Format tanggal tidak valid: {$date} (gunakan YYYY-MM-DD atau DD/MM/YYYY)");
        }
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
