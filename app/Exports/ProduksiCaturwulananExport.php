<?php

namespace App\Exports;

use App\Models\Produksi\ProduksiCaturwulanan;
use App\Models\Master\MasterKegiatan;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Carbon\Carbon;

class ProduksiCaturwulananExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize
{
    // Properti untuk menyimpan filter
    protected $dataRange;
    protected $dataFormat;
    protected $jenisKegiatan;
    protected $kegiatan;
    protected $search;
    protected $page;
    protected $perPage;
    protected $tahun;
    protected $currentModul = 'produksi_caturwulanan';

    // Constructor untuk menerima filter dari Controller
    public function __construct($dataRange, $dataFormat, $jenisKegiatan, $kegiatan, $search, $page, $perPage, $tahun)
    {
        $this->dataRange = $dataRange;
        $this->dataFormat = $dataFormat;
        $this->jenisKegiatan = $jenisKegiatan;
        $this->kegiatan = $kegiatan;
        $this->search = $search;
        $this->page = $page;
        $this->perPage = $perPage;
        $this->tahun = $tahun;
    }

    /**
     * Peta Prefix (Translator)
     * Logika yang SAMA PERSIS dengan yang ada di Controller.
     */
    private function getPrefixMap($jenisKegiatan)
    {
        $map = [
            'ubinan'       => ['UbinanPadiPalawija'],
            'updating utp' => ['UpdatingUTPPalawija']
        ];
        
        $jenisKegiatanLower = strtolower($jenisKegiatan);
        
        // Di sini kita tidak 'abort(404)', tapi kembalikan array kosong jika tidak cocok
        if (!isset($map[$jenisKegiatanLower])) {
            return [];
        }
        
        return $map[$jenisKegiatanLower];
    }

    /**
     * Kueri utama untuk mengambil data.
     * Ini adalah replika dari kueri di Controller->index()
     */
    public function query()
    {
        $validPrefixes = $this->getPrefixMap($this->jenisKegiatan);
        
        $query = ProduksiCaturwulanan::query()
            ->leftJoin('master_kegiatan', 'produksi_caturwulanan.master_kegiatan_id', '=', 'master_kegiatan.id_master_kegiatan')
            ->where(function($q) {
                $q->where('master_kegiatan.modul', $this->currentModul)
                  ->orWhereNull('produksi_caturwulanan.master_kegiatan_id'); 
            })
            ->where(function($q) use ($validPrefixes) {
                if (empty($validPrefixes)) { $q->whereRaw('1 = 0'); return; }
                foreach ($validPrefixes as $prefix) {
                    $q->orWhere('master_kegiatan.nama_kegiatan', 'LIKE', $prefix . '%')
                      ->orWhere(function($sub) use ($prefix) {
                          $sub->whereNull('produksi_caturwulanan.master_kegiatan_id')
                              ->where('produksi_caturwulanan.nama_kegiatan', 'LIKE', $prefix . '%');
                      });
                }
            })
            ->whereYear('produksi_caturwulanan.created_at', $this->tahun);

        // Terapkan filter lain dari constructor
        if ($this->kegiatan) {
            if (is_numeric($this->kegiatan)) {
                $query->where('produksi_caturwulanan.master_kegiatan_id', $this->kegiatan);
            } else {
                $query->whereNull('produksi_caturwulanan.master_kegiatan_id')
                      ->where('produksi_caturwulanan.nama_kegiatan', $this->kegiatan);
            }
        }

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('produksi_caturwulanan.BS_Responden', 'like', "%{$this->search}%")
                  ->orWhere('produksi_caturwulanan.pencacah', 'like', "%{$this->search}%")
                  ->orWhere('produksi_caturwulanan.pengawas', 'like', "%{$this->search}%")
                  ->orWhere('master_kegiatan.nama_kegiatan', 'like', "%{$this->search}%")
                  ->orWhere('produksi_caturwulanan.nama_kegiatan', 'like', "%{$this->search}%");
            });
        }

        // Handle pagination untuk ekspor
        if ($this->dataRange === 'current_page' && $this->perPage != -1) {
            $query->skip(($this->page - 1) * $this->perPage)->take($this->perPage);
        }
        
        // Select kolom utama dan order
        return $query->select('produksi_caturwulanan.*')->with('masterKegiatan')
                     ->latest('produksi_caturwulanan.id_produksi_caturwulanan');
    }

    /**
     * Mendefinisikan header kolom di file Excel.
     */
    public function headings(): array
    {
        return [
            'ID',
            'Nama Kegiatan',
            'BS/Responden',
            'Pencacah',
            'Pengawas',
            'Target Selesai',
            'Progress',
            'Tgl Kumpul',
        ];
    }

    /**
     * Memetakan data dari $row ke array untuk baris Excel.
     */
    public function map($row): array
    {
        $targetSelesai = $row->target_penyelesaian;
        $tglKumpul = $row->tanggal_pengumpulan;

        // Format tanggal berdasarkan pilihan pengguna di modal ekspor
        if ($this->dataFormat === 'formatted_values') {
            $targetSelesai = $targetSelesai ? Carbon::parse($targetSelesai)->format('d/m/Y') : '-';
            $tglKumpul = $tglKumpul ? Carbon::parse($tglKumpul)->format('d/m/Y') : '-';
        } else { // raw_values
            $targetSelesai = $targetSelesai ? Carbon::parse($targetSelesai)->format('Y-m-d') : null;
            $tglKumpul = $tglKumpul ? Carbon::parse($tglKumpul)->format('Y-m-d') : null;
        }

        return [
            $row->id_produksi_caturwulanan,
            $row->masterKegiatan->nama_kegiatan ?? $row->nama_kegiatan, // Tampilkan nama dari master jika ada
            $row->BS_Responden,
            $row->pencacah,
            $row->pengawas,
            $targetSelesai,
            $row->flag_progress,
            $tglKumpul,
        ];
    }
}