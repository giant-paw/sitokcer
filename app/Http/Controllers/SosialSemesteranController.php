<?php
// app/Http/Controllers/SosialSemesteranController.php

namespace App\Http\Controllers;

use App\Models\SosialSemesteran;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Carbon\Carbon;

class SosialSemesteranController extends Controller
{
    public function index(Request $request)
    {
        $kategori = $request->get('kategori', ''); // ex: "Sakernas"
        $q        = trim($request->get('q', ''));

        $query = SosialSemesteran::query();

        if ($kategori) {
            $query->where('nama_kegiatan', 'LIKE', "%$kategori%");
        }

        if ($q !== '') {
            $query->where(function ($w) use ($q) {
                $w->where('BS_Responden', 'LIKE', "%$q%")
                    ->orWhere('pencacah', 'LIKE', "%$q%")
                    ->orWhere('pengawas', 'LIKE', "%$q%")
                    ->orWhere('flag_progress', 'LIKE', "%$q%");
            });
        }

        $items = $query->orderBy('id_sosial_semesteran', 'asc')
            ->paginate(20)
            ->appends(['q' => $q, 'kategori' => $kategori]);

        // untuk auto-open modal ketika validasi gagal
        $openModal = session('openModal', false);

        return view('timSosial.semesteran.sakemas', compact('items', 'kategori', 'q', 'openModal'));
    }

    public function store(Request $r)
    {
        $data = $r->validate([
            'nama_kegiatan'       => ['required', 'string', 'max:60', 'regex:/^Sakernas/i'],
            'BS_Responden'        => 'nullable|string|max:150',
            'pencacah'            => 'required|string|max:100',
            'pengawas'            => 'required|string|max:100',
            'target_penyelesaian' => 'nullable|date',         // dari input type=date (Y-m-d)
            'flag_progress'       => ['required', Rule::in(['Belum Mulai', 'Proses', 'Selesai'])],
            'tanggal_pengumpulan' => 'nullable|date',         // dari input type=datetime-local
        ]);

        // Konversi tanggal sesuai skema penyimpanan (target_penyelesaian: d/m/Y, pengumpulan: Y-m-d H:i:s)
        if (!empty($data['target_penyelesaian'])) {
            $data['target_penyelesaian'] = Carbon::parse($data['target_penyelesaian'])->format('d/m/Y');
        }
        if (!empty($data['tanggal_pengumpulan'])) {
            $data['tanggal_pengumpulan'] = Carbon::parse($data['tanggal_pengumpulan'])->format('Y-m-d H:i:s');
        }

        SosialSemesteran::create($data);

        return redirect()
            ->route('sosial.semesteran.index', ['kategori' => 'Sakernas'])
            ->with('ok', 'Data berhasil ditambahkan.');
    }

    public function show(SosialSemesteran $semesteran)
    {
        return view('timSosial.semesteran.show', compact('semesteran'));
    }

    public function update(Request $r, SosialSemesteran $semesteran)
    {
        $data = $r->validate([
            'nama_kegiatan'       => ['required', 'string', 'max:60', 'regex:/^Sakernas/i'],
            'BS_Responden'        => 'nullable|string|max:150',
            'pencacah'            => 'required|string|max:100',
            'pengawas'            => 'required|string|max:100',
            'target_penyelesaian' => 'nullable|date',
            'flag_progress'       => ['required', Rule::in(['Belum Mulai', 'Proses', 'Selesai'])],
            'tanggal_pengumpulan' => 'nullable|date',
        ]);

        $data['target_penyelesaian'] = !empty($data['target_penyelesaian'])
            ? Carbon::parse($data['target_penyelesaian'])->format('d/m/Y') : null;

        $data['tanggal_pengumpulan'] = !empty($data['tanggal_pengumpulan'])
            ? Carbon::parse($data['tanggal_pengumpulan'])->format('Y-m-d H:i:s') : null;

        $semesteran->update($data);

        return redirect()
            ->route('sosial.semesteran.index', ['kategori' => 'Sakernas'])
            ->with('ok', 'Perubahan disimpan.');
    }

    public function destroy(SosialSemesteran $semesteran)
    {
        $semesteran->delete();
        return back()->with('ok', 'Data dihapus.');
    }
}
