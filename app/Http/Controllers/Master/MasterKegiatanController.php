<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\Master\MasterKegiatan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class MasterKegiatanController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');

        $kegiatan = MasterKegiatan::when($search, function ($query, $term) {
            $query->where('nama_kegiatan', 'like', "%{$term}%")
                  ->orWhere('deskripsi', 'like', "%{$term}%");
        })
        ->orderBy('nama_kegiatan', 'asc')
        ->paginate(15)
        ->withQueryString();

        return view('masterKegiatan.masterKegiatan', compact('kegiatan', 'search'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nama_kegiatan' => 'required|string|max:50|unique:master_kegiatan,nama_kegiatan',
            'deskripsi' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return back()
                    ->withErrors($validator)
                    ->withInput()
                    ->with('error_modal', 'tambahDataModal');
        }

        MasterKegiatan::create($validator->validated());

        return redirect()->route('master.kegiatan.index')->with('success', 'Kegiatan baru berhasil ditambahkan.');
    }

    public function edit(MasterKegiatan $kegiatan)
    {
        return response()->json($kegiatan);
    }

    public function update(Request $request, MasterKegiatan $kegiatan)
    {
        $validator = Validator::make($request->all(), [
            'nama_kegiatan' => [
                'required', 'string', 'max:50',
                Rule::unique('master_kegiatan')->ignore($kegiatan->id_master_kegiatan, 'id_master_kegiatan')
            ],
            'deskripsi' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return back()
                    ->withErrors($validator)
                    ->withInput()
                    ->with('error_modal', 'editDataModal')
                    ->with('edit_id', $kegiatan->id_master_kegiatan);
        }

        $kegiatan->update($validator->validated());

        return redirect()->route('master.kegiatan.index')->with('success', 'Data kegiatan berhasil diperbarui.');
    }

    public function destroy(MasterKegiatan $kegiatan)
    {
        try {
            $kegiatan->delete();
            return redirect()->route('master.kegiatan.index')->with('success', 'Data kegiatan berhasil dihapus.');
        } catch (\Illuminate\Database\QueryException $e) {
            // Tangkap error jika ada foreign key constraint
            return back()->with('error', 'Gagal menghapus: Kegiatan ini mungkin sedang digunakan di data lain.');
        }
    }

    public function bulkDelete(Request $request)
    {
        $request->validate(['ids' => 'required|array', 'ids.*' => 'exists:master_kegiatan,id_master_kegiatan']);

        try {
            MasterKegiatan::whereIn('id_master_kegiatan', $request->ids)->delete();
            return back()->with('success', 'Data kegiatan yang dipilih berhasil dihapus.');
        } catch (\Illuminate\Database\QueryException $e) {
            return back()->with('error', 'Gagal menghapus: Salah satu kegiatan mungkin sedang digunakan di data lain.');
        }
    }
}
