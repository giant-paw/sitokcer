namespace App\Exports;

use App\Models\SosialTahunan;
use Maatwebsite\Excel\Concerns\FromCollection;

class SosialTahunanExport implements FromCollection
{
protected $items;

public function __construct($items)
{
$this->items = $items;
}

public function collection()
{
return $this->items;
}
}