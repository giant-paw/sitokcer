<?php
// app/Http/Controllers/SosialSemesteranController.php
namespace App\Http\Controllers;

use App\Models\SosialSemesteran;
use Illuminate\Http\Request;

class SosialSemesteranController extends Controller
{
    public function index(Request $request)
    {
        // Filter kategori atau pencarian
        $kategori = $request->get('kategori', '');
        $q = trim($request->get('q', ''));

        $query = SosialSemesteran::query();

        if ($kategori) {
            $query->where('nama_kegiatan', 'LIKE', "%$kategori%");
        }

        if ($q) {
            $query->where(function ($query) use ($q) {
                $query->where('BS_Responden', 'LIKE', "%$q%")
                    ->orWhere('pencacah', 'LIKE', "%$q%")
                    ->orWhere('pengawas', 'LIKE', "%$q%")
                    ->orWhere('flag_progress', 'LIKE', "%$q%");
            });
        }

        // Ambil data dengan pagination
        $items = $query->orderBy('id_sosial_semesteran', 'asc')
            ->paginate(20)
            ->appends(['q' => $q, 'kategori' => $kategori]);

        return view('timSosial.semesteran.sakemas', compact('items', 'kategori', 'q'));
    }

    // Method lainnya tetap


    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
