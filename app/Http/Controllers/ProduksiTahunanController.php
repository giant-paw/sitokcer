<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ProduksiTahunan;
use Carbon\Carbon;

class ProduksiTahunanController extends Controller
{
    public function index(Request $req)
    {
        $q        = $req->q;
        $kategori = $req->kategori;

        $items = ProduksiTahunan::when($kategori, fn($w) => $w->where('nama_kegiatan', $kategori))
            ->when($q, function ($w) use ($q) {
                $w->where(function ($x) use ($q) {
                    $x->where('nama_kegiatan', 'like', "%$q%")
                        ->orWhere('BS_Responden', 'like', "%$q%")
                        ->orWhere('pencacah', 'like', "%$q%")
                        ->orWhere('pengawas', 'like', "%$q%");
                });
            })
            ->orderByDesc('id_produksi')
            ->paginate(20)
            ->withQueryString();

        return view('timProduksi.tahunan.produksitahunan', compact('items', 'q', 'kategori'));
    }

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
    public function store(Request $r)
    {
        $data = $r->validate([
            'nama_kegiatan'       => 'required|string|max:50',
            'BS_Responden'        => 'nullable|string|max:150',
            'pencacah'            => 'required|string|max:100',
            'pengawas'            => 'required|string|max:100',
            'target_penyelesaian' => 'required',
            'flag_progress'       => 'required|in:Belum Mulai,Proses,Selesai',
            'tanggal_pengumpulan' => 'nullable',
        ]);

        // ubah tanggal ke format d/m/Y (sesuai DB kamu)
        $data['target_penyelesaian'] = Carbon::parse($data['target_penyelesaian'])->format('d/m/Y');
        if (!empty($data['tanggal_pengumpulan'])) {
            $data['tanggal_pengumpulan'] = Carbon::parse($data['tanggal_pengumpulan'])->format('d/m/Y');
        }

        ProduksiTahunan::create($data);
        return back()->with('ok', 'Data berhasil ditambahkan.');
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

    public function destroy(ProduksiTahunan $tahunan)
    {
        $tahunan->delete();
        return back()->with('ok', 'Data berhasil dihapus.');
    }
}
