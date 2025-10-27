<?php

namespace App\Imports;
use App\Models\nwa\NwaTahunan;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsErrors;
use Illuminate\Support\Collection;
use Carbon\Carbon;

class NwaTahunanImport implements ToCollection, WithHeadingRow, SkipsOnError
{
    use SkipsErrors;

    protected $errors = [];
    protected $successCount = 0;

    public function collection(Collection $rows)
    {
        foreach ($rows as $index => $row) {
            $rowNumber = $index + 2; // Baris 1 adalah header, mulai dari baris 2

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

                // Parse tanggal (target_penyelesaian WAJIB, tanggal_pengumpulan OPSIONAL)
                $targetPenyelesaian = $this->parseDate($this->val($rowArray, 'target_penyelesaian'));
                $tanggalPengumpulan = $this->val($rowArray, 'tanggal_pengumpulan');

                // Parse tanggal pengumpulan jika ada
                if ($tanggalPengumpulan !== null && $tanggalPengumpulan !== '') {
                    try {
                        $tanggalPengumpulan = $this->parseDate($tanggalPengumpulan);
                    } catch (\Exception $e) {
                        $this->errors[] = [
                            'row' => $rowNumber,
                            'error' => 'Baris ' . $rowNumber . ': Format Tanggal Pengumpulan tidak valid'
                        ];
                        continue;
                    }
                } else {
                    $tanggalPengumpulan = null;
                }

                // Hitung tahun_kegiatan dari target_penyelesaian
                $tahunKegiatan = null;
                try {
                    $tahunKegiatan = Carbon::parse($targetPenyelesaian)->year;
                } catch (\Exception $e) {
                    // Jika gagal parse, biarkan null
                }

                // Insert data
                NwaTahunan::create([
                    'nama_kegiatan'       => $this->val($rowArray, 'nama_kegiatan'),
                    'BS_Responden'        => $this->val($rowArray, 'bs_responden'),
                    'pencacah'            => $this->val($rowArray, 'pencacah'),
                    'pengawas'            => $this->val($rowArray, 'pengawas'),
                    'target_penyelesaian' => $targetPenyelesaian,
                    'tahun_kegiatan'      => $tahunKegiatan,
                    'flag_progress'       => $this->val($rowArray, 'flag_progress'),
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

    /**
     * Helper: ambil nilai + trim spasi
     */
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

    /**
     * Validasi setiap baris
     */
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
            // tanggal_pengumpulan OPSIONAL, tidak masuk required
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

        // 5. VALIDASI FLAG PROGRESS (HANYA "Belum Selesai" ATAU "Selesai")
        $validFlags = ['Belum Selesai', 'Selesai', 'BELUM SELESAI', 'SELESAI', 'belum selesai', 'selesai'];
        $flagValue = $this->val($row, 'flag_progress');

        // Normalisasi untuk perbandingan case-insensitive
        if (!in_array($flagValue, $validFlags)) {
            return [
                'valid' => false,
                'message' => "Baris {$rowNumber}: Flag Progress hanya boleh: 'Belum Selesai' atau 'Selesai'"
            ];
        }

        // 6. VALIDASI FORMAT TANGGAL target_penyelesaian (WAJIB)
        try {
            $this->parseDate($this->val($row, 'target_penyelesaian'));
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

    /**
     * Parse tanggal dari berbagai format
     */
    protected function parseDate($date)
    {
        if ($date === null) {
            throw new \Exception("Tanggal wajib diisi dan tidak boleh kosong");
        }

        // Excel serial number
        if (is_numeric($date)) {
            try {
                return Carbon::instance(\PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($date))
                    ->format('Y-m-d');
            } catch (\Exception $e) {
                throw new \Exception("Format tanggal Excel tidak valid: {$date}");
            }
        }

        // Coba parse berbagai format
        $formats = ['Y-m-d', 'd/m/Y', 'd-m-Y', 'Y/m/d', 'm/d/Y'];
        foreach ($formats as $format) {
            try {
                return Carbon::createFromFormat($format, (string)$date)->format('Y-m-d');
            } catch (\Exception $e) {
                // coba format lain
            }
        }

        // Fallback parse bebas
        try {
            return Carbon::parse((string)$date)->format('Y-m-d');
        } catch (\Exception $e) {
            throw new \Exception("Format tanggal tidak valid: {$date} (gunakan YYYY-MM-DD atau DD/MM/YYYY)");
        }
    }

    /**
     * Get errors list
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * Get success count
     */
    public function getSuccessCount()
    {
        return $this->successCount;
    }
}