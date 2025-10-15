<?php

// app/Http/Controllers/SerutiController.php
namespace App\Http\Controllers;

use App\Models\SosialTriwulanan;
use Illuminate\Http\Request;

class SerutiController extends Controller
{
    public function index(Request $request)
    {
        // Tab aktif: TW1..TW4. Default TW1
        $tw = strtoupper($request->get('tw', 'TW1')); // TW1/TW2/TW3/TW4
        $q  = trim($request->get('q', ''));

        $query = SosialTriwulanan::query()
            ->where('nama_kegiatan', 'LIKE', "Seruti-$tw%");

        if ($q !== '') {
            $query->where(function ($w) use ($q) {
                $w->where('BS_Responden', 'LIKE', "%$q%")
                    ->orWhere('pencacah', 'LIKE', "%$q%")
                    ->orWhere('pengawas', 'LIKE', "%$q%")
                    ->orWhere('flag_progress', 'LIKE', "%$q%");
            });
        }

        $rows = $query->orderBy('id_sosial_triwulanan', 'asc')
            ->paginate(20)
            ->appends(['tw' => $tw, 'q' => $q]);

        return view('timSosial.seruti.seruti', compact('rows', 'tw', 'q'));
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
