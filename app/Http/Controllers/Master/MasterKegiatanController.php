<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\Master\MasterKegiatan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule; // <-- PENTING: Pastikan ini di-import

class MasterKegiatanController extends Controller
{
    /**
     * Definisikan daftar tim yang valid di satu tempat
     * agar mudah dikelola.
     */
    private $validTim = ['Tim Sosial', 'Tim Distribusi', 'Tim Produksi', 'Tim NWA'];

    /**
     * Tampilkan daftar kegiatan dengan filter.
     */
    public function index(Request $request)
    {
        $search = $request->input('search');
        // [BARU] Ambil filter_tim dari request
        $filter_tim = $request->input('filter_tim');

        $kegiatan = MasterKegiatan::query()
            // [BARU] Tambahkan filter 'when' untuk 'tim'
            ->when($filter_tim, function ($query, $tim) {
                return $query->where('tim', $tim);
            })
            // Filter 'when' untuk 'search'
            ->when($search, function ($query, $term) {
                // Kelompokkan 'where' pencarian agar tidak bentrok dengan filter 'tim'
                $query->where(function($q) use ($term) {
                    $q->where('nama_kegiatan', 'like', "%{$term}%")
                      ->orWhere('deskripsi', 'like', "%{$term}%")
                      ->orWhere('tim', 'like', "%{$term}%"); // [OPSIONAL] Cari berdasarkan tim juga
                });
            })
            // [DIUBAH] Mengurutkan berdasarkan data terbaru (ID tertinggi)
            ->latest('id_master_kegiatan') 
            ->paginate(15)
            ->withQueryString(); // withQueryString akan menangani parameter search & filter_tim

        // $search tidak wajib dikirim jika view menggunakan request('search')
        // Tapi kita biarkan saja karena kode Anda sebelumnya menggunakannya
        return view('masterKegiatan.masterKegiatan', compact('kegiatan', 'search'));
    }

    /**
     * Simpan data baru.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nama_kegiatan' => 'required|string|max:50|unique:master_kegiatan,nama_kegiatan',
            'deskripsi'     => 'nullable|string|max:255',
            // [BARU] Tambahkan validasi untuk 'tim'
            'tim'           => ['required', 'string', Rule::in($this->validTim)],
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

    /**
     * Ambil data untuk modal edit (AJAX).
     * (Tidak perlu diubah, 'tim' akan otomatis terkirim)
     */
    public function edit(MasterKegiatan $kegiatan)
    {
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
            // [BARU] Tambahkan validasi untuk 'tim'
            'tim'       => ['required', 'string', Rule::in($this->validTim)],
        ]);

        if ($validator->fails()) {
            // [PENTING] Ganti 'edit_error' agar sesuai dengan Blade Anda
            // Jika Anda menggunakan @error('nama_kegiatan', 'edit_error')
            // maka Error Bag harus dinamai 'edit_error'
            
            // [PERBAIKAN] Langsung arahkan error ke 'edit_error' agar sesuai dengan Blade
            $errorBagName = 'edit_error';
           
            return back()
                ->withErrors($validator, $errorBagName) // [PERBAIKAN] Tentukan error bag
                ->withInput()
                ->with('error_modal', 'editDataModal')
                ->with('edit_id', $kegiatan->id_master_kegiatan);
        }

        $kegiatan->update($validator->validated());

        return redirect()->route('master.kegiatan.index')->with('success', 'Data kegiatan berhasil diperbarui.');
    }

    /**
     * Hapus satu data.
     */
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

    /**
     * Hapus banyak data (Bulk).
     */
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

    /**
     * Fungsi pencarian untuk Autocomplete/AJAX (jika ada).
     */
    public function search(Request $request)
    {
        $query = $request->input('query', '');

        $data = MasterKegiatan::query()
            ->where('nama_kegiatan', 'LIKE', "%{$query}%")
            ->orWhere('deskripsi', 'LIKE', "%{$query}%")
            ->orWhere('tim', 'LIKE', "%{$query}%") // [BARU] Tambahkan pencarian 'tim'
            ->limit(10)
            ->pluck('nama_kegiatan');

        return response()->json($data);
    }
}