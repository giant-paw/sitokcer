<?php

namespace App\Http\Controllers\Sosial;

use App\Http\Controllers\Controller;
use App\Models\Sosial\SosialTahunan;
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

    /** Halaman create (opsional jika tidak pakai modal) */
    public function create(Request $req)
    {
        // prefill nama_kegiatan via query ?kategori=PODES|Polkam
        $prefill = $req->query('kategori');
        return view('timSosial.tahunan.create', compact('prefill'));
    }

    public function store(Request $r)
    {
        $data = $r->validate([
            'nama_kegiatan'       => 'required|string|max:50',
            'BS_Responden'        => 'nullable|string|max:150',
            'pencacah'            => 'required|string|max:100',
            'pengawas'            => 'required|string|max:100',
            'target_penyelesaian' => 'required', // Y-m-d dari input date
            'flag_progress'       => 'required|in:Belum Mulai,Proses,Selesai',
            'tanggal_pengumpulan' => 'nullable',
        ]);

        // Simpan ke DB sebagai d/m/Y (varchar) sesuai struktur database kamu
        $data['target_penyelesaian'] = self::toDMY($data['target_penyelesaian']);
        if (!empty($data['tanggal_pengumpulan'])) {
            $data['tanggal_pengumpulan'] = self::toDMY($data['tanggal_pengumpulan']);
        }

        SosialTahunan::create($data);

        return redirect()
            ->route('sosial.tahunan.index', ['kategori' => $data['nama_kegiatan'] ?? null])
            ->with('ok', 'Data berhasil ditambahkan.');
    }

    public function show(SosialTahunan $tahunan)
    {
        return view('timSosial.tahunan.show', compact('tahunan'));
    }

    public function edit(SosialTahunan $tahunan)
    {
        // Siapkan nilai Y-m-d untuk prefill input date
        $targetYmd = self::toYMD($tahunan->target_penyelesaian);
        $kumpulYmd = self::toYMD($tahunan->tanggal_pengumpulan);

        return view('timSosial.tahunan.create', [
            'mode'      => 'edit',
            'tahunan'   => $tahunan,
            'prefill'   => $tahunan->nama_kegiatan,
            'targetYmd' => $targetYmd,
            'kumpulYmd' => $kumpulYmd,
        ]);
    }

    public function update(Request $r, SosialTahunan $tahunan)
    {
        $data = $r->validate([
            'nama_kegiatan'       => 'required|string|max:50',
            'BS_Responden'        => 'nullable|string|max:150',
            'pencacah'            => 'required|string|max:100',
            'pengawas'            => 'required|string|max:100',
            'target_penyelesaian' => 'required',
            'flag_progress'       => 'required|in:Belum Mulai,Proses,Selesai',
            'tanggal_pengumpulan' => 'nullable',
        ]);

        $data['target_penyelesaian'] = self::toDMY($data['target_penyelesaian']);
        if (!empty($data['tanggal_pengumpulan'])) {
            $data['tanggal_pengumpulan'] = self::toDMY($data['tanggal_pengumpulan']);
        } else {
            $data['tanggal_pengumpulan'] = null;
        }

        $tahunan->update($data);

        return redirect()
            ->route('sosial.tahunan.index', ['kategori' => $data['nama_kegiatan'] ?? null])
            ->with('ok', 'Data berhasil diperbarui.');
    }

    public function destroy(SosialTahunan $tahunan)
    {
        $tahunan->delete();
        return back()->with('ok', 'Data dihapus.');
    }

    // ================= Helpers =================

    /** '2025-07-31' -> '31/07/2025' ; jika sudah d/m/Y dibiarkan */
    private static function toDMY(string $value): string
    {
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
            return Carbon::createFromFormat('Y-m-d', $value)->format('d/m/Y');
        }
        try {
            return Carbon::createFromFormat('d/m/Y', $value)->format('d/m/Y');
        } catch (\Throwable $e) {
            return $value;
        }
    }

    /** '31/07/2025' -> '2025-07-31' ; jika sudah Y-m-d dibiarkan */
    private static function toYMD(?string $value): ?string
    {
        if (!$value) return null;
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) return $value;

        try {
            return Carbon::createFromFormat('d/m/Y', $value)->format('Y-m-d');
        } catch (\Throwable $e) {
            return $value;
        }
    }

    public function bulkDelete(Request $request)
    {
        $ids = $request->input('ids'); // Ambil array id yang dikirim melalui checkbox

        if ($ids) {
            // Hapus data berdasarkan id yang terpilih
            SosialTahunan::whereIn('id_sosial_tahunan', $ids)->delete();
            return redirect()->route('sosial.tahunan.index')->with('ok', 'Data berhasil dihapus.');
        }

        return back()->with('error', 'Tidak ada data yang dipilih.');
    }
}
