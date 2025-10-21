<?php

namespace App\Http\Controllers\Sosial;

use App\Http\Controllers\Controller;
use App\Models\Sosial\SosialSemesteran;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Carbon\Carbon;

class SosialSemesteranController extends Controller
{
    public function index(Request $request, $kategori = 'Sakernas') // Default ke Sakernas jika tidak ada
    {
        $semester = $request->get('semester', 'S1'); // Default ke Semester 1
        $q = trim($request->get('q', ''));

        $query = SosialSemesteran::query();

        // Filter berdasarkan kategori (Sakernas/Susenas)
        $query->where('nama_kegiatan', 'LIKE', "%{$kategori}%");

        // [PERBAIKAN] Filter berdasarkan bulan yang sesuai dengan semester
        $semester1Months = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni'];
        $semester2Months = ['Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];

        $monthsToSearch = ($semester === 'S1') ? $semester1Months : $semester2Months;

        // Terapkan filter bulan menggunakan orWhere di dalam sebuah grup
        $query->where(function ($subQuery) use ($monthsToSearch) {
            foreach ($monthsToSearch as $month) {
                $subQuery->orWhere('nama_kegiatan', 'LIKE', '%' . $month . '%');
            }
        });


        // Filter berdasarkan query pencarian
        if ($q !== '') {
            $query->where(function ($w) use ($q) {
                $w->where('BS_Responden', 'LIKE', "%$q%")
                    ->orWhere('pencacah', 'LIKE', "%$q%")
                    ->orWhere('pengawas', 'LIKE', "%$q%");
            });
        }

        $rows = $query->orderBy('id_sosial_semesteran', 'asc')
            ->paginate(request('per_page', 20))
            ->appends($request->except('page')); // Mempertahankan semua query string

        // Ganti nama variabel 'items' menjadi 'rows' agar konsisten dengan view
        return view('timSosial.semesteran.index', [
            'rows' => $rows,
            'kategori' => $kategori,
            'semester' => $semester,
            'q' => $q,
        ]);
    }

    public function store(Request $r, $kategori)
    {
        $data = $r->validate([
            'nama_kegiatan' => ['required', 'string', 'max:100'],
            'BS_Responden' => ['nullable', 'string', 'max:150'],
            'pencacah' => ['required', 'string', 'max:100'],
            'pengawas' => ['required', 'string', 'max:100'],
            'target_penyelesaian' => ['nullable', 'date'],
            'flag_progress' => ['required', Rule::in(['Belum Mulai', 'Proses', 'Selesai'])],
            'tanggal_pengumpulan' => ['nullable', 'date'],
            'semester' => ['required', Rule::in(['S1', 'S2'])],
        ]);

        // [PERBAIKAN] Hapus konversi format yang salah. Biarkan Laravel yang menangani.
        // Data dari form dengan type="date" sudah dalam format Y-m-d yang benar.

        SosialSemesteran::create($data);

        return redirect()
            ->route('sosial.semesteran.index', ['kategori' => $kategori, 'semester' => $data['semester']])
            ->with('success', 'Data ' . $kategori . ' berhasil ditambahkan.');
    }

    // Method 'show' Anda (jika ada)

    public function update(Request $r, $kategori, SosialSemesteran $semesteran)
    {
        $data = $r->validate([
            'nama_kegiatan' => ['required', 'string', 'max:100'],
            'BS_Responden' => ['nullable', 'string', 'max:150'],
            'pencacah' => ['required', 'string', 'max:100'],
            'pengawas' => ['required', 'string', 'max:100'],
            'target_penyelesaian' => ['nullable', 'date'],
            'flag_progress' => ['required', Rule::in(['Belum Mulai', 'Proses', 'Selesai'])],
            'tanggal_pengumpulan' => ['nullable', 'date'],
        ]);

        // [PERBAIKAN] Hapus konversi format yang salah untuk semua tanggal.

        $semesteran->update($data);

        return back()->with('success', 'Perubahan disimpan.');
    }

    public function destroy($kategori, SosialSemesteran $semesteran)
    {
        $semesteran->delete();
        return back()->with('success', 'Data dihapus.');
    }

    /**
     * [BARU] Menghapus beberapa data sekaligus (bulk delete).
     */
    public function bulkDelete(Request $request, $kategori)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'integer|exists:sosial_semesteran,id_sosial_semesteran' // Ganti tabel & primary key
        ]);

        $ids = $request->input('ids');
        SosialSemesteran::whereIn('id_sosial_semesteran', $ids)->delete();

        return back()->with('success', count($ids) . ' data berhasil dihapus.');
    }
}

