<?php

namespace App\Http\Controllers;

use App\Models\SosialTriwulanan;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Carbon\Carbon;

class SerutiController extends Controller
{
    public function index(Request $request)
    {
        $tw = strtoupper($request->get('tw', 'TW1')); // TW1..TW4
        $q  = trim($request->get('q', ''));

        $query = SosialTriwulanan::query()
            ->where('nama_kegiatan', 'LIKE', "Seruti-$tw%");

        if ($q !== '') {
            $query->where(function ($w) use ($q) {
                $w->where('BS_Responden', 'LIKE', "%$q%")
                    ->orWhere('pencacah', 'LIKE', "%$q%")
                    ->orWhere('pengawas', 'LIKE', "%$q%")
                    ->orWhere('flag_progress', 'LIKE', "%$q%");
            });
        }

        $rows = $query->orderBy('id_sosial_triwulanan', 'asc')
            ->paginate(20)
            ->appends(['tw' => $tw, 'q' => $q]);

        return view('timSosial.seruti.seruti', compact('rows', 'tw', 'q'));
    }

    public function create(Request $request)
    {
        $tw = strtoupper($request->get('tw', 'TW1'));
        return view('timSosial.seruti.create_edit', [
            'mode'        => 'create',
            'tw'          => $tw,
            'defaultNama' => "Seruti-$tw",
            'targetYmd'   => null,
            'kumpulLocal' => null,
        ]);
    }

    public function store(Request $r)
    {
        $data = $r->validate([
            // ganti Rule::regex(...) -> 'regex:...'
            'nama_kegiatan'       => ['required', 'string', 'max:50', 'regex:/^Seruti-(TW1|TW2|TW3|TW4)/'],
            'BS_Responden'        => 'nullable|string|max:150',
            'pencacah'            => 'required|string|max:100',
            'pengawas'            => 'required|string|max:100',
            'target_penyelesaian' => 'nullable|date',
            'flag_progress'       => ['required', Rule::in(['Belum Mulai', 'Proses', 'Selesai'])],
            'tanggal_pengumpulan' => 'nullable|date',
        ]);

        if (!empty($data['tanggal_pengumpulan'])) {
            $data['tanggal_pengumpulan'] = Carbon::parse($data['tanggal_pengumpulan'])->format('Y-m-d H:i:s');
        }

        SosialTriwulanan::create($data);

        $tw = self::extractTW($data['nama_kegiatan']);
        return redirect()->route('sosial.seruti.index', ['tw' => $tw])->with('ok', 'Data Seruti ditambahkan.');
    }

    public function show(SosialTriwulanan $seruti)
    {
        return view('timSosial.seruti.show', compact('seruti'));
    }

    public function edit(SosialTriwulanan $seruti)
    {
        return view('timSosial.seruti.create_edit', [
            'mode'        => 'edit',
            'tw'          => self::extractTW($seruti->nama_kegiatan),
            'defaultNama' => $seruti->nama_kegiatan,
            'seruti'      => $seruti,
            'targetYmd'   => $seruti->target_penyelesaian
                ? Carbon::parse($seruti->target_penyelesaian)->format('Y-m-d') : null,
            'kumpulLocal' => $seruti->tanggal_pengumpulan
                ? Carbon::parse($seruti->tanggal_pengumpulan)->format('Y-m-d\TH:i') : null,
        ]);
    }

    public function update(Request $r, SosialTriwulanan $seruti)
    {
        $data = $r->validate([
            'nama_kegiatan'       => ['required', 'string', 'max:50', 'regex:/^Seruti-(TW1|TW2|TW3|TW4)/'],
            'BS_Responden'        => 'nullable|string|max:150',
            'pencacah'            => 'required|string|max:100',
            'pengawas'            => 'required|string|max:100',
            'target_penyelesaian' => 'nullable|date',
            'flag_progress'       => ['required', Rule::in(['Belum Mulai', 'Proses', 'Selesai'])],
            'tanggal_pengumpulan' => 'nullable|date',
        ]);

        $data['tanggal_pengumpulan'] = !empty($data['tanggal_pengumpulan'])
            ? Carbon::parse($data['tanggal_pengumpulan'])->format('Y-m-d H:i:s')
            : null;

        $seruti->update($data);

        $tw = self::extractTW($data['nama_kegiatan']);
        return redirect()->route('sosial.seruti.index', ['tw' => $tw])->with('ok', 'Perubahan disimpan.');
    }


    public function destroy(SosialTriwulanan $seruti)
    {
        $tw = self::extractTW($seruti->nama_kegiatan);
        $seruti->delete();
        return redirect()->route('sosial.seruti.index', ['tw' => $tw])->with('ok', 'Data dihapus.');
    }

    private static function extractTW(string $nama): string
    {
        return preg_match('/Seruti\-(TW[1-4])/', $nama, $m) ? $m[1] : 'TW1';
    }
}
