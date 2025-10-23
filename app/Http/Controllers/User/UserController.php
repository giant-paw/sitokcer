<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\User\User; 
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB; 
use Illuminate\Support\Facades\Hash; 
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UserController extends Controller
{
    /**
     * Menampilkan daftar user.
     */
    public function index(Request $request)
    {
        $selectedTahun = $request->input('tahun', date('Y'));
        $availableTahun = User::query()
            ->select(DB::raw('YEAR(created_at) as tahun'))
            ->distinct()->whereNotNull('created_at')->orderBy('tahun', 'desc')
            ->pluck('tahun')->toArray();
        if (empty($availableTahun) || !in_array(date('Y'), $availableTahun)) {
            array_unshift($availableTahun, date('Y'));
        }

        $query = User::query();
        $selectedTim = $request->input('tim', '');
        if ($selectedTim !== '') {
            $query->where('tim', $selectedTim);
        }

        $search = $request->input('search', '');
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                  ->orWhere('username', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('tim', 'like', "%{$search}%");
            });
        }

        // 3. Logika Pagination
        $perPage = $request->input('per_page', 20);
        if ($perPage == 'all') {
            $total = (clone $query)->count();
            $perPage = $total > 0 ? $total : 20;
        }

        // 4. Ambil Data (Gunakan primary key 'id_user')
        $listData = $query->latest('id_user')->paginate($perPage)->withQueryString();

        $timCounts = User::query()
            ->select('tim', DB::raw('count(*) as total'))
            ->groupBy('tim')
            ->orderBy('tim')
            ->get();


        // 6. Kirim ke View
        return view('user.user', compact( 
            'listData',
            'timCounts', 
            'availableTahun', 
            'selectedTahun', 
            'selectedTim',    
            'search'
        ));
    }

    /**
     * Simpan user baru (AJAX ready).
     */
    public function store(Request $request)
    {
        $baseRules = [
            'nama'     => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:user,username', // Pastikan unique di tabel 'user'
            'email'    => 'required|string|email|max:255|unique:user,email',   // Pastikan unique di tabel 'user'
            'password' => ['required', 'string', Password::min(6)->letters()->numbers(), 'confirmed'], // Contoh aturan password, butuh 'password_confirmation'
            'tim'      => 'nullable|string|max:100', // Sesuaikan max length jika perlu
        ];

        $customMessages = [
            'username.unique' => 'Username ini sudah digunakan.',
            'email.unique'    => 'Email ini sudah terdaftar.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
        ];

        $validator = Validator::make($request->all(), $baseRules, $customMessages);

        if ($validator->fails()) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['message' => 'Data tidak valid.', 'errors' => $validator->errors()], 422);
            }
            return back()->withErrors($validator)->withInput()->with('error_modal', 'tambahDataModal');
        }

        $validatedData = $validator->validated();


        User::create($validatedData);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => 'User berhasil ditambahkan!']);
        }
        return back()->with(['success' => 'User berhasil ditambahkan!', 'auto_hide' => true]);
    }

    /**
     * Ambil data user untuk modal edit (AJAX ready).
     * Menggunakan $id manual.
     */
    public function edit($id)
    {
        $user = User::findOrFail($id);
        // Jangan kirim password ke client!
        return response()->json($user->makeHidden('password'));
    }
    
    public function update(Request $request, $id)
    {
        // Jangan update user admin utama (misal ID 1) sembarangan
        if ($id == 1 && auth()->user()->id_user != 1) { // Hanya admin utama yg bisa edit dirinya
             return response()->json(['message' => 'Anda tidak diizinkan mengedit user ini.'], 403);
             // Atau redirect back() jika non-AJAX
        }

        $user = User::findOrFail($id);

        $baseRules = [
            'nama'     => 'required|string|max:255',
            // Rule unique di-update untuk mengabaikan user saat ini
            'username' => ['required', 'string', 'max:255', Rule::unique('user', 'username')->ignore($user->id_user, 'id_user')],
            'email'    => ['required', 'string', 'email', 'max:255', Rule::unique('user', 'email')->ignore($user->id_user, 'id_user')],
            // Password opsional saat update
            'password' => ['nullable', 'string', Password::min(6)->letters()->numbers(), 'confirmed'],
            'tim'      => 'nullable|string|max:100',
        ];

        $customMessages = [ /* ... sama seperti store ... */ ];
        $validator = Validator::make($request->all(), $baseRules, $customMessages);

        if ($validator->fails()) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['message' => 'Data tidak valid.', 'errors' => $validator->errors()], 422);
            }
            return back()->withErrors($validator)->withInput()
                ->with('error_modal', 'editDataModal')
                ->with('edit_id', $user->id_user);
        }

        $validatedData = $validator->validated();

        // Hanya update password jika diisi
        if (empty($validatedData['password'])) {
            unset($validatedData['password']); // Hapus password dari data update jika kosong
        }
        // Hashing sudah dihandle oleh Model

        $user->update($validatedData);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => 'User berhasil diperbarui!']);
        }
        return back()->with(['success' => 'User berhasil diperbarui!', 'auto_hide' => true]);
    }

    /**
     * Hapus satu user (AJAX ready).
     * Menggunakan $id manual.
     */
    public function destroy($id)
    {
        // Jangan biarkan user menghapus dirinya sendiri atau admin utama
        if ($id == auth()->user()->id_user) {
            return back()->with(['error' => 'Anda tidak bisa menghapus akun Anda sendiri.', 'auto_hide' => true]);
        }
        if ($id == 1) { // Asumsi ID 1 adalah admin utama
             return back()->with(['error' => 'User admin utama tidak bisa dihapus.', 'auto_hide' => true]);
        }

        $user = User::findOrFail($id);
        $user->delete();

        if (request()->ajax() || request()->wantsJson()) {
             return response()->json(['success' => 'User berhasil dihapus!']);
        }
        return back()->with(['success' => 'User berhasil dihapus!', 'auto_hide' => true]);
    }

    /**
     * Hapus banyak user.
     */
    public function bulkDelete(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:user,id_user' // Validasi ke tabel 'user', pk 'id_user'
        ]);

        $idsToDelete = $request->ids;

        // Filter agar tidak menghapus user yg sedang login atau admin utama (ID 1)
        $currentUser = auth()->user();
        $idsToDelete = array_filter($idsToDelete, function($id) use ($currentUser) {
            return $id != $currentUser->id_user && $id != 1;
        });

        if (empty($idsToDelete)) {
             return back()->with(['error' => 'Tidak ada user valid yang dipilih untuk dihapus (User Anda atau Admin Utama tidak bisa dihapus).', 'auto_hide' => true]);
        }

        User::whereIn('id_user', $idsToDelete)->delete();

        return back()->with(['success' => 'User yang dipilih berhasil dihapus!', 'auto_hide' => true]);
    }
}