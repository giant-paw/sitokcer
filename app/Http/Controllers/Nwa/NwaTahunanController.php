<?php

namespace App\Http\Controllers\Nwa;

use App\Http\Controllers\Controller;
use App\Models\Nwa\NwaTahunan;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Carbon\Carbon;

class NwaTahunanController extends Controller
{
    public function index(Request $request)
    {
        $kategori = trim($request->get('kategori', '')); // filter by nama_kegiatan
        $q        = trim($request->get('q', ''));

        $query = NwaTahunan::query();

        if ($kategori !== '') {
            $query->where('nama_kegiatan', $kategori);
        }

        if ($q !== '') {
            $query->where(function ($w) use ($q) {
                $w->where('BS_Responden', 'LIKE', "%$q%")
                    ->orWhere('pencacah', 'LIKE', "%$q%")
                    ->orWhere('pengawas', 'LIKE', "%$q%")
                    ->orWhere('flag_progress', 'LIKE', "%$q%");
            });
        }

        // [MODIFIED] Handle 'per_page' request for display dropdown
        $perPage = $request->input('per_page', 20);
        if ($perPage === 'all') {
            $rows = $query->orderBy('id_nwa', 'asc')->get();
        } else {
            $rows = $query->orderBy('id_nwa', 'asc')
                ->paginate((int)$perPage)
                ->appends($request->except('page')); // appends all query strings
        }

        // daftar kategori + jumlah (untuk pill/tautan di atas)
        $katMap = NwaTahunan::selectRaw('nama_kegiatan as kategori, COUNT(*) as jml')
            ->groupBy('nama_kegiatan')
            ->orderBy('kategori')
            ->get()
            ->map(fn($r) => ['label' => $r->kategori, 'count' => (int)$r->jml])
            ->all();

        return view('timNWA.tahunan.NWAtahunan', [
            'rows'      => $rows,
            'q'         => $q,
            'kategori'  => $kategori,
            'kategoris' => $katMap,
        ]);
    }

    public function store(Request $r)
    {
        $data = $r->validate([
            'nama_kegiatan'       => ['required', 'string', 'max:100'],
            'BS_Responden'        => ['nullable', 'string', 'max:150'],
            'pencacah'            => ['required', 'string', 'max:100'],
            'pengawas'            => ['required', 'string', 'max:100'],
            'target_penyelesaian' => ['nullable', 'date'],
            'flag_progress'       => ['required', Rule::in(['Belum Mulai', 'Proses', 'Selesai'])],
            'tanggal_pengumpulan' => ['nullable', 'date'],
        ]);

        if (!empty($data['tanggal_pengumpulan'])) {
            $data['tanggal_pengumpulan'] = Carbon::parse($data['tanggal_pengumpulan'])->format('Y-m-d H:i:s');
        }

        NwaTahunan::create($data);

        return back()->with('ok', 'Data NWA Tahunan ditambahkan.');
    }

    public function show(NwaTahunan $tahunan)
    {
        return view('timNWA.tahunan.show', compact('tahunan'));
    }

    public function update(Request $r, NwaTahunan $tahunan)
    {
        $data = $r->validate([
            'nama_kegiatan'       => ['required', 'string', 'max:100'],
            'BS_Responden'        => ['nullable', 'string', 'max:150'],
            'pencacah'            => ['required', 'string', 'max:100'],
            'pengawas'            => ['required', 'string', 'max:100'],
            'target_penyelesaian' => ['nullable', 'date'],
            'flag_progress'       => ['required', Rule::in(['Belum Mulai', 'Proses', 'Selesai'])],
            'tanggal_pengumpulan' => ['nullable', 'date'],
        ]);

        $data['tanggal_pengumpulan'] = !empty($data['tanggal_pengumpulan'])
            ? Carbon::parse($data['tanggal_pengumpulan'])->format('Y-m-d H:i:s')
            : null;

        $tahunan->update($data);

        return back()->with('ok', 'Perubahan disimpan.');
    }

    public function destroy(NwaTahunan $tahunan)
    {
        $tahunan->delete();
        return back()->with('ok', 'Data dihapus.');
    }

    /**
     * [BARU] Menghapus beberapa data tahunan sekaligus (bulk delete).
     */
    public function bulkDelete(Request $request)
    {
        // 1. Validasi request
        $request->validate([
            'ids'   => 'required|array',
            'ids.*' => 'integer|exists:nwa_tahunan,id_nwa' // Validasi bahwa semua ID ada
        ]);

        $ids = $request->input('ids');

        // 2. Hapus data berdasarkan ID yang dipilih
        NwaTahunan::whereIn('id_nwa', $ids)->delete();

        // 3. Redirect kembali dengan pesan sukses
        return redirect()->route('nwa.tahunan.index')
                         ->with('ok', count($ids) . ' data berhasil dihapus.');
    }
}
