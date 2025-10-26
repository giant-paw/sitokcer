<?php

namespace App\Exports;

use App\Models\Master\MasterPetugas;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class MasterPetugasExport implements FromCollection, WithHeadings
{
    protected $dataRange;
    protected $search;
    protected $currentPage;
    protected $perPage;

    /**
     * Membuat export baru
     * @param string $dataRange      // 'all' atau 'current_page'
     * @param string|null $search    // filter pencarian
     * @param int $currentPage       // halaman aktif (default: 1)
     * @param int $perPage           // data per halaman (default: 15)
     */
    public function __construct($dataRange = 'all', $search = null, $currentPage = 1, $perPage = 15)
    {
        $this->dataRange   = $dataRange;
        $this->search      = $search;
        $this->currentPage = (int) $currentPage;
        $this->perPage     = (int) $perPage;
    }

    public function collection()
    {
        $query = MasterPetugas::query();

        // Filter search (nama, nik, kategori)
        if ($this->search) {
            $query->where(function ($q) {
                $q->where('nama_petugas', 'like', "%{$this->search}%")
                  ->orWhere('nik', 'like', "%{$this->search}%")
                  ->orWhere('kategori', 'like', "%{$this->search}%");
            });
        }

        // Urutkan terbaru
        $query->orderBy('created_at', 'desc');

        // Ambil cuma current page jika dipilih
        if ($this->dataRange === 'current_page') {
            $offset = ($this->currentPage - 1) * $this->perPage;
            $query->offset($offset)->limit($this->perPage);
        }

        // Ambil data dan mapping format tanggal lahir biar rapi
        return $query->get()->map(function ($p) {
            return [
                $p->nama_petugas,
                $p->kategori,
                $p->nik,
                $p->alamat,
                $p->no_hp,
                $p->posisi,
                $p->email,
                $p->pendidikan,
                $p->tgl_lahir ? ($p->tgl_lahir instanceof \Carbon\Carbon ? $p->tgl_lahir->format('d/m/Y') : $p->tgl_lahir) : '',
                $p->kecamatan,
                $p->pekerjaan,
            ];
        });
    }

    public function headings(): array
    {
        return [
            'Nama Petugas',
            'Kategori',
            'NIK',
            'Alamat',
            'No HP',
            'Posisi',
            'Email',
            'Pendidikan',
            'Tanggal Lahir',
            'Kecamatan',
            'Pekerjaan',
        ];
    }
}