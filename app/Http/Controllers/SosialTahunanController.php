<?php

// app/Http/Controllers/SosialTahunanController.php
namespace App\Http\Controllers;

use App\Models\SosialTahunan;
use Illuminate\Http\Request;
use Carbon\Carbon;

class SosialTahunanController extends Controller
{
    public function index(Request $req)
    {
        $q        = $req->q;
        $kategori = $req->kategori; // 'Polkam' | 'PODES' | null

        $countPolkam = SosialTahunan::where('nama_kegiatan', 'Polkam')->count();
        $countPodes  = SosialTahunan::where('nama_kegiatan', 'PODES')->count();

        $items = SosialTahunan::when($kategori, fn($w) => $w->where('nama_kegiatan', $kategori))
            ->when($q, function ($w) use ($q) {
                $w->where(function ($x) use ($q) {
                    $x->where('nama_kegiatan', 'like', "%$q%")
                        ->orWhere('BS_Responden', 'like', "%$q%")
                        ->orWhere('pencacah', 'like', "%$q%")
                        ->orWhere('pengawas', 'like', "%$q%");
                });
            })
            ->orderByDesc('id_sosial')
            ->paginate(20)
            ->withQueryString();

        // jika validasi gagal, kita buka modal otomatis
        $openModal = session('openModal', false);

        return view('timSosial.tahunan.sosialtahunan', compact(
            'items',
            'q',
            'kategori',
            'countPolkam',
            'countPodes',
            'openModal'
        ));
    }

    public function store(Request $r)
    {
        // validasi dulu
        $data = $r->validate([
            'nama_kegiatan'       => 'required|string|max:50',
            'BS_Responden'        => 'nullable|string|max:150',
            'pencacah'            => 'required|string|max:100',
            'pengawas'            => 'required|string|max:100',
            'target_penyelesaian' => 'required',       // date string; kita konversi manual
            'flag_progress'       => 'required|in:Belum Mulai,Proses,Selesai',
            'tanggal_pengumpulan' => 'nullable',
        ]);

        // --- KONVERSI TANGGAL ---
        // browser (type=date) -> 'Y-m-d'; DB kamu simpan dd/mm/YYYY (varchar)
        $data['target_penyelesaian'] = self::toDMY($data['target_penyelesaian']);
        if (!empty($data['tanggal_pengumpulan'])) {
            $data['tanggal_pengumpulan'] = self::toDMY($data['tanggal_pengumpulan']);
        }

        SosialTahunan::create($data);

        return redirect()
            ->route('sosial.tahunan.index', ['kategori' => $data['nama_kegiatan'] ?? null])
            ->with('ok', 'Data berhasil ditambahkan.');
    }

    private static function toDMY(string $value): string
    {
        // jika sudah d/m/Y, biarkan; jika Y-m-d, konversi
        // contoh input: '2025-07-31' => '31/07/2025'
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
            return Carbon::createFromFormat('Y-m-d', $value)->format('d/m/Y');
        }
        // coba baca d/m/Y
        try {
            return Carbon::createFromFormat('d/m/Y', $value)->format('d/m/Y');
        } catch (\Throwable $e) {
            return $value; // fallback
        }
    }

    public function destroy(SosialTahunan $tahunan)
    {
        $tahunan->delete();
        return back()->with('ok', 'Data dihapus.');
    }
}
