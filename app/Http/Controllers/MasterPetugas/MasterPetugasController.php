<?php

namespace App\Http\Controllers\MasterPetugas;

use App\Http\Controllers\Controller;
use App\Models\MasterPetugas\MasterPetugas;
use Illuminate\Http\Request;

class MasterPetugasController extends Controller
{
    public function index(Request $request)
    {
        $q = $request->input('q');

        $petugas = MasterPetugas::when($q, function ($query, $q) {
            $query->where('nama_petugas', 'like', "%$q%")
                ->orWhere('nik', 'like', "%$q%")
                ->orWhere('kategori', 'like', "%$q%");
        })
        ->orderBy('nama_petugas', 'asc')
        ->paginate(10)
        ->appends(['q' => $q]);

        return view('master.petugas.index', compact('petugas', 'q'));
    }

    public function store(Request $request)
    {
        // Validasi sederhana
        $request->validate([
            'nama_petugas' => 'required|string|max:255',
            'email' => 'nullable|email',
            'nik' => 'nullable|string|max:20',
        ]);

        MasterPetugas::create($request->all());

        return redirect()->route('master.petugas.index')->with('success', 'Data petugas berhasil ditambahkan.');
    }

    public function update(Request $request, MasterPetugas $petugas)
    {
        // Validasi sederhana
        $request->validate([
            'nama_petugas' => 'required|string|max:255',
            'email' => 'nullable|email',
            'nik' => 'nullable|string|max:20',
        ]);
        
        $petugas->update($request->all());

        return redirect()->route('master.petugas.index')->with('success', 'Data petugas berhasil diperbarui.');
    }


    public function bulkDelete(Request $r)
    {
        MasterPetugas::whereIn('id_petugas', $r->ids)->delete();
        return response()->json(['status' => 'ok']);
    }

    public function export()
    {
        $petugas = MasterPetugas::all();
        $csv = "Nama,Kategori,NIK,Alamat,No HP,Posisi,Email\n";
        foreach ($petugas as $p) {
            $csv .= "\"$p->nama_petugas\",\"$p->kategori\",\"$p->nik\",\"$p->alamat\",\"$p->no_hp\",\"$p->posisi\",\"$p->email\"\n";
        }
        $filename = 'master_petugas_' . date('Ymd_His') . '.csv';
        return response($csv)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', "attachment; filename=$filename");
    }
}

