<?php

namespace App\Exports;

use App\Models\Sosial\SosialTriwulanan; 
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Carbon\Carbon;

class SosialTriwulananExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    protected $dataRange;
    protected $dataFormat;
    protected $kegiatan;
    protected $search;
    protected $tahun;
    protected $currentPage;
    protected $perPage;
    protected $kegiatanPrefix;

    /**
     * Terima semua parameter filter dari controller
     */
    public function __construct(
        $dataRange, $dataFormat, $kegiatan, $search, $tahun, $currentPage, $perPage, $kegiatanPrefix
    ) {
        $this->dataRange      = $dataRange;
        $this->dataFormat     = $dataFormat;
        $this->kegiatan       = $kegiatan;
        $this->search         = $search;
        $this->tahun          = $tahun;
        $this->currentPage    = $currentPage;
        $this->perPage        = $perPage;
        $this->kegiatanPrefix = $kegiatanPrefix;
    }

    /**
     * Buat query yang sama persis dengan di fungsi index controller
     * @return \Illuminate\Database\Query\Builder
     */
    public function query()
    {
        // 1. Kueri Utama (Meniru fungsi index)
        $query = SosialTriwulanan::query()
            ->where('nama_kegiatan', 'LIKE', $this->kegiatanPrefix . '%') // Filter by Prefix
            ->whereYear('created_at', $this->tahun);

        // 2. Filter Kegiatan Spesifik (Tab)
        if ($this->kegiatan !== '' && $this->kegiatan !== null) {
            if (is_numeric($this->kegiatan)) {
                $query->where('master_kegiatan_id', $this->kegiatan);
            } else {
                $query->whereNull('master_kegiatan_id')
                      ->where('nama_kegiatan', $this->kegiatan);
            }
        }

        // 3. Filter Pencarian
        if ($this->search !== '' && $this->search !== null) {
            $query->where(function ($q) {
                $q->where('BS_Responden', 'like', "%{$this->search}%")
                  ->orWhere('pencacah', 'like', "%{$this->search}%")
                  ->orWhere('pengawas', 'like', "%{$this->search}%")
                  ->orWhere('nama_kegiatan', 'like', "%{$this->search}%");
            });
        }

        // 4. Urutan dan Relasi
        $query->with('masterKegiatan')
              ->latest('id_sosial_triwulanan'); // Sesuaikan PK

        // 5. Logika Jangkauan Data (Penting!)
        if ($this->dataRange === 'current_page' && $this->perPage != -1) {
             // Jika 'Hanya Halaman Ini', terapkan skip dan take
            $query->skip(($this->currentPage - 1) * $this->perPage)
                  ->take($this->perPage);
        }
        // Jika 'Semua Data', biarkan query mengambil semua hasil.

        return $query;
    }

    /**
     * Tentukan header kolom
     * @return array
     */
    public function headings(): array
    {
        // Sesuaikan dengan template import Anda
        return [
            'Nama Kegiatan',
            'BS/Responden',
            'Pencacah',
            'Pengawas',
            'Target Penyelesaian',
            'Flag Progress',
            'Tanggal Pengumpulan',
        ];
    }

    /**
     * Petakan data untuk setiap baris
     * @param mixed $item
     * @return array
     */
    public function map($item): array
    {
        // $item adalah hasil dari query (instance SosialTriwulanan)
        
        // Ambil nama dari relasi jika ada, jika tidak, gunakan nama di tabel
        $namaKegiatan = $item->masterKegiatan->nama_kegiatan ?? $item->nama_kegiatan;

        return [
            $namaKegiatan,
            $item->BS_Responden,
            $item->pencacah,
            $item->pengawas,
            $this->formatDate($item->target_penyelesaian),
            $item->flag_progress,
            $this->formatDate($item->tanggal_pengumpulan),
        ];
    }

    /**
     * Helper untuk format tanggal berdasarkan pilihan user di modal
     */
    private function formatDate($date)
    {
        if (!$date) {
            return null;
        }

        // Pastikan $date adalah objek Carbon
        $carbonDate = ($date instanceof Carbon) ? $date : Carbon::parse($date);

        if ($this->dataFormat === 'raw_values') {
            // Nilai Asli Database (yyyy-mm-dd)
            return $carbonDate->format('Y-m-d');
        }
        
        // Format Tampilan (dd/mm/yyyy) - Default
        return $carbonDate->format('d/m/Y');
    }
    
    /**
     * Terapkan style (opsional, tapi bagus)
     */
    public function styles(Worksheet $sheet)
    {
        return [
            // Style baris header
            1    => [
                'font' => ['bold' => true],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['argb' => 'FFD9EAD3'] // Hijau muda (sesuai template)
                ]
            ],
        ];
    }
}