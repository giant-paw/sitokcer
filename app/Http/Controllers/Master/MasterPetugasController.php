<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\Master\MasterPetugas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator; 
use Illuminate\Validation\Rule; 
use Symfony\Component\HttpFoundation\StreamedResponse;

class MasterPetugasController extends Controller
{

    public function index(Request $request)
    {
        $query = MasterPetugas::query();

        $search = $request->input('search');
        $query->when($search, function ($q, $term) {
            $q->where('nama_petugas', 'like', "%{$term}%")
              ->orWhere('nik', 'like', "%{$term}%")
              ->orWhere('kategori', 'like', "%{$term}%");
        });

        $perPage = $request->input('per_page', 15);

        if ($perPage == 'all') {
            $total = (clone $query)->count();
            $perPage = $total > 0 ? $total : 15;
        }

        $petugas = $query->orderBy('created_at', 'desc')
                         ->paginate($perPage)
                         ->withQueryString();

        return view('masterPetugas.masterPetugas', compact('petugas', 'search'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nama_petugas' => 'required|string|max:255',
            'kategori' => 'required|string|max:100',
            'nik' => 'required|string|max:20|unique:master_petugas,nik',
            'alamat' => 'required|string',
            'no_hp' => 'required|string|max:20',
            'posisi' => 'required|string|max:100',
            'email' => 'required|email|max:255|unique:master_petugas,email',
            'pendidikan' => 'required|string|max:100',
            'tgl_lahir' => 'nullable|date_format:Y-m-d',
            'kecamatan' => 'required|string|max:100',
            'pekerjaan' => 'required|string|max:100',
        ]);

        if ($validator->fails()) {
            return back()
                    ->withErrors($validator)
                    ->withInput()
                    ->with('error_modal', 'tambahDataModal');
        }

        $validatedData = $validator->validated();

        MasterPetugas::create($validatedData); 

        return redirect()->route('master.petugas.index')->with('success', 'Data petugas berhasil ditambahkan.');
    }

    
    public function edit(MasterPetugas $petugas)
    {
        if ($petugas->tgl_lahir instanceof \Carbon\Carbon) {
             $petugas->tgl_lahir_formatted = $petugas->tgl_lahir->format('Y-m-d');
        } else {
             $petugas->tgl_lahir_formatted = null; // Atau format default jika bukan objek Carbon
        }
        return response()->json($petugas);
    }

   
    public function update(Request $request, MasterPetugas $petugas)
    {
        $validator = Validator::make($request->all(), [
            'nama_petugas' => 'required|string|max:255',
            'kategori' => 'nullable|string|max:100',
            'nik' => [
                'nullable', 'string', 'max:20',
                Rule::unique('master_petugas')->ignore($petugas->id_petugas, 'id_petugas')
            ],
            'alamat' => 'nullable|string',
            'no_hp' => 'nullable|string|max:20',
            'posisi' => 'nullable|string|max:100',
            'email' => [
                'nullable', 'email', 'max:255',
                Rule::unique('master_petugas')->ignore($petugas->id_petugas, 'id_petugas')
            ],
            'pendidikan' => 'nullable|string|max:100',
            'tgl_lahir' => 'nullable|date_format:Y-m-d',
            'kecamatan' => 'nullable|string|max:100',
            'pekerjaan' => 'nullable|string|max:100',
        ]);

        if ($validator->fails()) {
            return back()
                    ->withErrors($validator)
                    ->withInput()
                    ->with('error_modal', 'editDataModal')
                    ->with('edit_id', $petugas->id_petugas); 
        }

        $validatedData = $validator->validated();

        $petugas->update($validatedData);

        return redirect()->route('master.petugas.index')->with('success', 'Data petugas berhasil diperbarui.');
    }

    public function destroy(MasterPetugas $petugas)
    {
        $petugas->delete();
        return redirect()->route('master.petugas.index')->with('success', 'Data petugas berhasil dihapus.');
    }

    public function bulkDelete(Request $request)
    {
        $request->validate([
            'ids'   => 'required|array',
            'ids.*' => 'exists:master_petugas,id_petugas'
        ]);

        MasterPetugas::whereIn('id_petugas', $request->ids)->delete();
        return back()->with('success', 'Data petugas yang dipilih berhasil dihapus.');
    }

    public function export()
    {
        $filename = 'master_petugas_' . date('Ymd_His') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv; charset=utf-8', 
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        return new StreamedResponse(function () {
            $petugasCollection = MasterPetugas::orderBy('nama_petugas')->cursor();
            $handle = fopen('php://output', 'w');
            
            $header = ['Nama Petugas', 'Kategori', 'NIK', 'Alamat', 'No HP', 'Posisi', 'Email', 'Pendidikan', 'Tgl Lahir', 'Kecamatan', 'Pekerjaan'];
            fputcsv($handle, $header);

            foreach ($petugasCollection as $p) {
                fputcsv($handle, [
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
                ]);
            }
            fclose($handle);
        }, 200, $headers);
    }

    public function search(Request $request)
    {
        $request->validate(['query' => 'nullable|string|max:100']);
        $query = $request->input('query', '');

        $data = MasterPetugas::where('nama_petugas', 'LIKE', "%{$query}%")
                             ->limit(10)
                             ->pluck('nama_petugas');
        return response()->json($data);
    }
}