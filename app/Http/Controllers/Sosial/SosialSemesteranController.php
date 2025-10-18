<?php

namespace App\Http\Controllers\Sosial;

use App\Http\Controllers\Controller;
use App\Models\Sosial\SosialSemesteran;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Carbon\Carbon;

class SosialSemesteranController extends Controller
{
    public function index(Request $request)
    {
        $kategori = $request->get('kategori', ''); // Mendapatkan kategori dari URL

        $query = SosialSemesteran::query();

        // Menyaring berdasarkan kategori yang dipilih
        if ($kategori === 'Sakernas') {
            $query->where('nama_kegiatan', 'LIKE', '%Sakernas%');
        } elseif ($kategori === 'Susenas') {
            $query->where('nama_kegiatan', 'LIKE', '%Susenas%');
        }

        // Ambil data sesuai kategori yang dipilih
        $items = $query->orderBy('id_sosial_semesteran', 'asc')
            ->paginate(20)
            ->appends(['kategori' => $kategori]); // Menambahkan kategori ke URL agar tetap terlihat

        return view('timSosial.semesteran.index', compact('items', 'kategori'));
    }

    public function store(Request $r)
    {
        $data = $r->validate([
            'nama_kegiatan'       => ['required', 'string', 'max:60', 'regex:/^Susenas/i'], // Menyaring untuk Susenas
            'BS_Responden'        => 'nullable|string|max:150',
            'pencacah'            => 'required|string|max:100',
            'pengawas'            => 'required|string|max:100',
            'target_penyelesaian' => 'nullable|date',
            'flag_progress'       => ['required', Rule::in(['Belum Mulai', 'Proses', 'Selesai'])],
            'tanggal_pengumpulan' => 'nullable|date',
        ]);

        // Konversi tanggal sesuai skema penyimpanan
        if (!empty($data['target_penyelesaian'])) {
            $data['target_penyelesaian'] = Carbon::parse($data['target_penyelesaian'])->format('d/m/Y');
        }
        if (!empty($data['tanggal_pengumpulan'])) {
            $data['tanggal_pengumpulan'] = Carbon::parse($data['tanggal_pengumpulan'])->format('Y-m-d H:i:s');
        }

        SosialSemesteran::create($data);

        return redirect()
            ->route('sosial.semesteran.index', ['kategori' => 'Susenas'])  // Redirect ke halaman Susenas
            ->with('ok', 'Data Susenas berhasil ditambahkan.');
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
