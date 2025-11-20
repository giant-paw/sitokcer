<?php

namespace App\Imports;

use App\Models\Sosial\SosialSemesteran; // <-- [GANTI]
use App\Models\Master\MasterKegiatan;
// use App\Models\Master\MasterPetugas; // <-- Dihapus, validasi tidak dipakai
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsErrors;
use Carbon\Carbon;

class SosialSemesteranImport implements ToCollection, WithHeadingRow, SkipsOnError // <-- [GANTI]
{
    use SkipsErrors;

    protected $errors = [];
    protected $successCount = 0;
    protected $masterKegiatanMap;
    // protected $masterPetugasMap; // <-- Dihapus
    protected $currentModul;

    /**
     * [LOGIKA BARU DARI DISTRIBUSI]
     * Konstruktor diubah agar lebih aman dari spasi tersembunyi
     */
    public function __construct($currentModul)
    {
        $this->currentModul = $currentModul;
        
        // Ambil data dan buat map, SAYA TAMBAHKAN 'trim()' untuk keamanan
        $kegiatanData = MasterKegiatan::where('modul', $this->currentModul)
                                      ->get(['id_master_kegiatan', 'nama_kegiatan']);

        $this->masterKegiatanMap = $kegiatanData->mapWithKeys(function ($item) {
            return [ trim($item->nama_kegiatan) => $item->id_master_kegiatan ];
        });
        
        // Peta petugas dihapus, karena validasi tidak dipakai (mengikuti contoh Distribusi)
        // $this->masterPetugasMap = MasterPetugas::pluck('id_master_petugas', 'nama_petugas');
    }

    /**
     * [LOGIKA BARU DARI DISTRIBUSI]
     */
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

                SosialSemesteran::create($validation['data']); // <-- [GANTI]
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
     * [LOGIKA BARU DARI DISTRIBUSI]
     * Validasi disederhanakan: Hanya Kegiatan.
     */
    protected function validateRow($row, $rowNumber)
    {
        $dataToCreate = []; 

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
        $dataToCreate['master_kegiatan_id'] = $this->masterKegiatanMap[$namaKegiatan]; 
        $dataToCreate['nama_kegiatan'] = $namaKegiatan; 

        // 3. Ambil Data Lain (TANPA VALIDASI - Mengikuti contoh Distribusi)
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

        // 5. Masukkan sisa data (Mengikuti contoh Distribusi)
        if (isset($dataToCreate['target_penyelesaian'])) {
            $dataToCreate['tahun_kegiatan'] = Carbon::parse($dataToCreate['target_penyelesaian'])->year;
        } else {
             return ['valid' => false, 'message' => "Target Penyelesaian tidak valid"];
        }

        // Semua validasi lolos, kembalikan data bersih
        return ['valid' => true, 'data' => $dataToCreate];
    }

    /**
     * [LOGIKA BARU DARI DISTRIBUSI]
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
             try {
                return Carbon::instance(\PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($date))
                       ->format('Y-m-d H:i:s');
             } catch (\Exception $e) { /* Lanjut coba parser di bawah */ }
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