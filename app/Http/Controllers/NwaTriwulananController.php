<?php

namespace App\Http\Controllers;

use App\Models\NwaTriwulanan;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Carbon\Carbon;

class NwaTriwulananController extends Controller
{
    /** Map slug menu -> prefix nama kegiatan */
    private const JENIS_MAP = [
        'sklnp'  => 'SKLNP',
        'snaper' => 'Snaper',
        'sktnp'  => 'SKTNP',
    ];

    /** List halaman (satu view untuk semua jenis) */
    public function index(Request $request, string $jenis)
    {
        $prefix = self::prefixOf($jenis);          // SKLNP / Snaper / SKTNP
        $tw     = strtoupper($request->get('tw', 'TW1'));  // TW1..TW4
        $q      = trim($request->get('q', ''));

        $query = NwaTriwulanan::query()
            ->whereRaw("TRIM(nama_kegiatan) LIKE ?", ["{$prefix}-{$tw}%"]);


        if ($q !== '') {
            $query->where(function ($w) use ($q) {
                $w->where('BS_Responden', 'LIKE', "%$q%")
                    ->orWhere('pencacah', 'LIKE', "%$q%")
                    ->orWhere('pengawas', 'LIKE', "%$q%")
                    ->orWhere('flag_progress', 'LIKE', "%$q%");
            });
        }

        $rows = $query->orderBy('id_nwa_triwulanan', 'asc')
            ->paginate(20)
            ->appends(['tw' => $tw, 'q' => $q]);

        return view('timNWA.triwulanan.index', [
            'rows'   => $rows,
            'jenis'  => $jenis,      // slug
            'prefix' => $prefix,     // label
            'tw'     => $tw,
            'q'      => $q,
        ]);
    }

    /** Simpan data baru */
    public function store(Request $r, string $jenis)
    {
        $prefix = self::prefixOf($jenis);

        $data = $r->validate([
            'nama_kegiatan'       => ['required', 'string', 'max:100', "regex:/^{$prefix}\-(TW1|TW2|TW3|TW4)/"],
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

        NwaTriwulanan::create($data);

        $tw = self::extractTW($data['nama_kegiatan']);
        return redirect()->route('nwa.triwulanan.index', [$jenis, 'tw' => $tw])
            ->with('ok', 'Data NWA Triwulanan ditambahkan.');
    }

    /** Update data */
    public function update(Request $r, string $jenis, NwaTriwulanan $triwulanan)
    {
        $prefix = self::prefixOf($jenis);

        $data = $r->validate([
            'nama_kegiatan'       => ['required', 'string', 'max:100', "regex:/^{$prefix}\-(TW1|TW2|TW3|TW4)/"],
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

        $triwulanan->update($data);

        $tw = self::extractTW($data['nama_kegiatan']);
        return redirect()->route('nwa.triwulanan.index', [$jenis, 'tw' => $tw])
            ->with('ok', 'Perubahan disimpan.');
    }

    /** Hapus data */
    public function destroy(string $jenis, NwaTriwulanan $triwulanan)
    {
        $tw = self::extractTW($triwulanan->nama_kegiatan);
        $triwulanan->delete();

        return redirect()->route('nwa.triwulanan.index', [$jenis, 'tw' => $tw])
            ->with('ok', 'Data dihapus.');
    }

    /* ===== Helpers ===== */

    private static function prefixOf(string $jenis): string
    {
        $jenis = strtolower($jenis);
        return self::JENIS_MAP[$jenis] ?? 'SKLNP';
    }

    private static function extractTW(string $nama): string
    {
        return preg_match('/\-(TW[1-4])/', $nama, $m) ? $m[1] : 'TW1';
    }
}
