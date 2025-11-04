<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\Master\MasterKegiatan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class MasterKegiatanController extends Controller
{
    /**
     * Definisikan daftar tim & modul yang valid di satu tempat.
     */
    private $validTim = ['Tim Sosial', 'Tim Distribusi', 'Tim Produksi', 'Tim NWA'];
    // [TAMBAHAN] Definisikan modul yang valid
    private $validModul = [
        'sosial_tahunan', 'sosial_triwulanan', 'sosial_semesteran', 
        'distribusi_tahunan', 'distribusi_triwulanan', 'distribusi_bulanan', 
        'produksi_tahunan', 'produksi_caturwulanan', 'produksi_triwulanan', 'produksi_bulanan',
        'nwa_tahunan', 'nwa_triwulanan'
    
    ]; 

    public function index(Request $request)
    {
        $search = $request->input('search');
        $filter_tim = $request->input('filter_tim');
        $filter_modul = $request->input('filter_modul'); // [TAMBAHAN] Ambil filter modul

        $kegiatan = MasterKegiatan::query()
            // Filter berdasarkan Tim
            ->when($filter_tim, function ($query, $tim) {
                return $query->where('tim', $tim);
            })
            // [TAMBAHAN] Filter berdasarkan Modul
            ->when($filter_modul, function ($query, $modul) {
                return $query->where('modul', $modul);
            })
            // Filter berdasarkan Search
            ->when($search, function ($query, $term) {
                $query->where(function($q) use ($term) {
                    $q->where('nama_kegiatan', 'like', "%{$term}%")
                      ->orWhere('deskripsi', 'like', "%{$term}%")
                      ->orWhere('tim', 'like', "%{$term}%")
                      ->orWhere('target', 'like', "%{$term}%")
                      ->orWhere('modul', 'like', "%{$term}%"); // [TAMBAHAN] Cari di kolom modul
                });
            })
            ->latest('id_master_kegiatan')
            ->paginate(15)
            ->withQueryString();

        // Kirim $validModul ke view untuk dropdown filter
        $validModulOptions = $this->validModul; // Ambil dari properti

        return view('masterKegiatan.masterKegiatan', compact(
            'kegiatan', 
            'search',
            'validModulOptions' // [TAMBAHAN] Kirim data modul ke view
        ));
    }

    /**
     * Simpan data baru.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nama_kegiatan' => 'required|string|max:50|unique:master_kegiatan,nama_kegiatan',
            'deskripsi'     => 'nullable|string|max:255',
            'tim'           => ['required', 'string', Rule::in($this->validTim)],
            'modul'         => ['required', 'string', Rule::in($this->validModul)], // [TAMBAHAN] Validasi modul
            'target'        => 'nullable|integer|min:0',
        ],[
            'modul.required' => 'Modul harus dipilih.', // [TAMBAHAN] Custom message
            'modul.in' => 'Modul yang dipilih tidak valid.' // [TAMBAHAN] Custom message
        ]);

        if ($validator->fails()) {
            return back()
                ->withErrors($validator)
                ->withInput()
                ->with('error_modal', 'tambahDataModal');
        }

        // $validatedData sudah otomatis berisi 'modul' karena ada di $fillable Model
        MasterKegiatan::create($validator->validated());

        return redirect()->route('master.kegiatan.index')->with('success', 'Kegiatan baru berhasil ditambahkan.');
    }

    public function edit(MasterKegiatan $kegiatan)
    {
        // JSON sudah otomatis menyertakan 'modul'
        return response()->json($kegiatan);
    }

    /**
     * Perbarui data.
     */
    public function update(Request $request, MasterKegiatan $kegiatan)
    {
        $validator = Validator::make($request->all(), [
            'nama_kegiatan' => [
                'required', 'string', 'max:50',
                Rule::unique('master_kegiatan')->ignore($kegiatan->id_master_kegiatan, 'id_master_kegiatan')
            ],
            'deskripsi' => 'nullable|string|max:255',
            'tim'       => ['required', 'string', Rule::in($this->validTim)],
            'modul'     => ['required', 'string', Rule::in($this->validModul)], // [TAMBAHAN] Validasi modul
            'target'    => 'nullable|integer|min:0',
        ],[
            'modul.required' => 'Modul harus dipilih.', // [TAMBAHAN] Custom message
            'modul.in' => 'Modul yang dipilih tidak valid.' // [TAMBAHAN] Custom message
        ]);

        if ($validator->fails()) {
            $errorBagName = 'edit_error'; // Error bag untuk modal edit
            
            return back()
                ->withErrors($validator, $errorBagName)
                ->withInput()
                ->with('error_modal', 'editDataModal')
                ->with('edit_id', $kegiatan->id_master_kegiatan);
        }

        // $validatedData sudah otomatis berisi 'modul'
        $kegiatan->update($validator->validated());

        return redirect()->route('master.kegiatan.index')->with('success', 'Data kegiatan berhasil diperbarui.');
    }

    /**
     * Hapus satu data. (Tidak perlu diubah)
     */
    public function destroy(MasterKegiatan $kegiatan)
    {
        try {
            $kegiatan->delete();
            return redirect()->route('master.kegiatan.index')->with('success', 'Data kegiatan berhasil dihapus.');
        } catch (\Illuminate\Database\QueryException $e) {
            // Cek kode error SQLSTATE[23000] (Integrity constraint violation)
            if ($e->getCode() === '23000') {
                 return back()->with('error', 'Gagal menghapus: Kegiatan ini sedang digunakan di data lain (misal: Distribusi Triwulanan). Hapus data terkait terlebih dahulu.');
            }
            return back()->with('error', 'Gagal menghapus data kegiatan. Error: ' . $e->getMessage());
        }
    }

    /**
     * Hapus banyak data. (Tidak perlu diubah)
     */
    public function bulkDelete(Request $request)
    {
        $request->validate(['ids' => 'required|array', 'ids.*' => 'exists:master_kegiatan,id_master_kegiatan']);

        try {
            MasterKegiatan::whereIn('id_master_kegiatan', $request->ids)->delete();
            return back()->with('success', 'Data kegiatan yang dipilih berhasil dihapus.');
        } catch (\Illuminate\Database\QueryException $e) {
             if ($e->getCode() === '23000') {
                 return back()->with('error', 'Gagal menghapus: Salah satu kegiatan yang dipilih mungkin sedang digunakan di data lain. Hapus data terkait terlebih dahulu.');
             }
            return back()->with('error', 'Gagal menghapus data kegiatan. Error: ' . $e->getMessage());
        }
    }

    /**
     * Search Autocomplete (Tidak perlu diubah)
     * Biasanya autocomplete hanya untuk nama.
     */
    public function search(Request $request)
    {
        $query = $request->input('query', '');

        $data = MasterKegiatan::query()
            ->where('nama_kegiatan', 'LIKE', "%{$query}%")
            // ->orWhere('deskripsi', 'LIKE', "%{$query}%") // Opsional
            // ->orWhere('tim', 'LIKE', "%{$query}%")       // Opsional
            // ->orWhere('target', 'LIKE', "%{$query}%")    // Opsional
            // ->orWhere('modul', 'LIKE', "%{$query}%")     // Opsional
            ->limit(10)
            ->pluck('nama_kegiatan');

        return response()->json($data);
    }
}