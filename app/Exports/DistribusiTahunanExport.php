<?php

namespace App\Exports;

use App\Models\DistribusiTahunan;
use Maatwebsite\Excel\Concerns\FromCollection;

class DistribusiTahunanExport implements FromCollection
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return DistribusiTahunan::all();
    }
}
