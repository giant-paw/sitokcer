<?php

namespace App\Http\Controllers\MasterPetugas;

use App\Http\Controllers\Controller;
use App\Models\MasterPetugas\MasterPetugas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator; 
use Illuminate\Validation\Rule; 
use Symfony\Component\HttpFoundation\StreamedResponse;

class MasterPetugasController extends Controller
{
    public function index(Request $request)
    {
        // ▼▼▼ PERUBAHAN DI SINI ▼▼▼
        $search = $request->input('search'); // Ganti 'q' menjadi 'search'

        $petugas = MasterPetugas::when($search, function ($query, $term) { // Ganti $q menjadi $term (atau $search)
            // Logika pencarian
            $query->where('nama_petugas', 'like', "%{$term}%")
                  ->orWhere('nik', 'like', "%{$term}%")
                  ->orWhere('kategori', 'like', "%{$term}%");
        })
        ->orderBy('nama_petugas', 'asc')
        ->paginate(15)
        // ▼▼▼ PERUBAHAN DI SINI ▼▼▼
        ->appends(['search' => $search]); // Ganti 'q' menjadi 'search'

        // ▼▼▼ PERUBAHAN DI SINI ▼▼▼
        return view('masterPetugas.masterPetugas', compact('petugas', 'search')); // Kirim 'search' bukan 'q'
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nama_petugas' => 'required|string|max:255',
            'kategori' => 'nullable|string|max:100', // Sesuaikan aturan (misal: in:Mitra,"Organik BPS")
            'nik' => 'nullable|string|max:20|unique:master_petugas,nik',
            'alamat' => 'nullable|string',
            'no_hp' => 'nullable|string|max:20',
            'posisi' => 'nullable|string|max:100',
            'email' => 'nullable|email|max:255|unique:master_petugas,email',
            'pendidikan' => 'nullable|string|max:100',
            'tgl_lahir' => 'nullable|date_format:Y-m-d', // Validasi format tanggal YYYY-MM-DD
            'kecamatan' => 'nullable|string|max:100',
            'pekerjaan' => 'nullable|string|max:100',
        ]);

        if ($validator->fails()) {
            return back()
                    ->withErrors($validator)
                    ->withInput()
                    ->with('error_modal', 'tambahDataModal');
        }

        // Ambil data yang sudah divalidasi saja
        $validatedData = $validator->validated();
        
        // (Opsional) Konversi format tanggal jika input dari form berbeda
        // if (isset($validatedData['tgl_lahir'])) {
        //     $validatedData['tgl_lahir'] = Carbon::createFromFormat('d/m/Y', $validatedData['tgl_lahir'])->format('Y-m-d');
        // }

        MasterPetugas::create($validatedData); // Lebih aman pakai validatedData

        return redirect()->route('master.petugas.index')->with('success', 'Data petugas berhasil ditambahkan.');
    }

    /**
     * Mengembalikan data petugas dalam format JSON untuk modal edit.
     */
    public function edit(MasterPetugas $petugas)
    {
        // Format tanggal agar sesuai dengan input type="date"
        if ($petugas->tgl_lahir instanceof \Carbon\Carbon) {
             $petugas->tgl_lahir_formatted = $petugas->tgl_lahir->format('Y-m-d');
        } else {
             $petugas->tgl_lahir_formatted = null; // Atau format default jika bukan objek Carbon
        }
        return response()->json($petugas);
    }

    /**
     * Memperbarui data petugas setelah validasi.
     */
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
                    ->with('edit_id', $petugas->id_petugas); // Kirim ID agar modal bisa dibuka lagi
        }

        $validatedData = $validator->validated();
        
        // (Opsional) Konversi format tanggal jika perlu
        // if (isset($validatedData['tgl_lahir'])) {
        //     $validatedData['tgl_lahir'] = Carbon::createFromFormat('d/m/Y', $validatedData['tgl_lahir'])->format('Y-m-d');
        // }

        $petugas->update($validatedData);

        return redirect()->route('master.petugas.index')->with('success', 'Data petugas berhasil diperbarui.');
    }

    /**
     * Menghapus satu data petugas.
     */
    public function destroy(MasterPetugas $petugas)
    {
        $petugas->delete();
        return redirect()->route('master.petugas.index')->with('success', 'Data petugas berhasil dihapus.');
    }

    /**
     * Menghapus beberapa data petugas (bulk).
     */
    public function bulkDelete(Request $request)
    {
        $request->validate([
            'ids'   => 'required|array',
            'ids.*' => 'exists:master_petugas,id_petugas'
        ]);

        MasterPetugas::whereIn('id_petugas', $request->ids)->delete();
        return back()->with('success', 'Data petugas yang dipilih berhasil dihapus.');
    }

    /**
     * Mengekspor data petugas ke CSV.
     */
    public function export()
    {
        $filename = 'master_petugas_' . date('Ymd_His') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv; charset=utf-8', // Tambahkan charset utf-8
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        return new StreamedResponse(function () {
            // Gunakan eager loading jika ada relasi, atau lazy() jika data sangat besar
            $petugasCollection = MasterPetugas::orderBy('nama_petugas')->cursor(); // Cursor lebih efisien memori
            $handle = fopen('php://output', 'w');
            
            // Tulis BOM UTF-8 (opsional, agar Excel membaca karakter non-latin dgn benar)
            // fwrite($handle, "\xEF\xBB\xBF");

            // Header CSV (HANYA field yang relevan)
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
                    $p->tgl_lahir ? ($p->tgl_lahir instanceof \Carbon\Carbon ? $p->tgl_lahir->format('d/m/Y') : $p->tgl_lahir) : '', // Format tanggal
                    $p->kecamatan,
                    $p->pekerjaan,
                ]);
            }
            fclose($handle);
        }, 200, $headers);
    }

    /**
     * Mencari nama petugas untuk autocomplete global.
     */
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