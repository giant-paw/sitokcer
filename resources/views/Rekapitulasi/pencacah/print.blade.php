{{-- resources/views/Rekapitulasi/pencacah/print.blade.php --}}
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Rekapitulasi Pencacah</title>
    <style>
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 12px; }
        .container { width: 100%; margin: 0 auto; }
        .text-center { text-align: center; }
        .text-start { text-align: left; }
        .mb-3 { margin-bottom: 1rem; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #dee2e6; padding: 8px; }
        thead th { background-color: #f8f9fa; font-weight: bold; }
        .main-row td { font-weight: bold; background-color: #f1f3f5; }
        .activity-table { margin: 8px 0; width: 95%; border: none; }
        .activity-table th, .activity-table td { font-size: 11px; padding: 5px; border: 1px solid #e9ecef; }
        .activity-table th { background-color: #fff; }
        @page { size: A4; margin: 20mm; }
        @media print { body { -webkit-print-color-adjust: exact; } }
    </style>
</head>
<body onload="window.print()">
    <div class="container">
        <h2 class="text-center mb-3">Laporan Rekapitulasi Pencacah</h2>
        @if($q)
            <p>Hasil pencarian untuk: <strong>{{ $q }}</strong></p>
        @endif
        <table>
            <thead>
                <tr>
                    <th class="text-center" style="width: 5%;">#</th>
                    <th class="text-start" style="width: 45%;">Pencacah</th>
                    <th class="text-center" style="width: 15%;">Total Responden</th>
                    <th class="text-start" style="width: 35%;">Detail Kegiatan</th>
                </tr>
            </thead>
            <tbody>
                @forelse($rekapPencacah as $index => $pencacah)
                    <tr class="main-row">
                        <td class="text-center">{{ $index + 1 }}</td>
                        <td>{{ $pencacah->nama_pencacah }}</td>
                        <td class="text-center">{{ $pencacah->total_responden }}</td>
                        <td>
                            @if(count($pencacah->kegiatan) > 0)
                                <table class="activity-table">
                                    <thead>
                                        <tr>
                                            <th>Nama Kegiatan</th>
                                            <th class="text-center" style="width: 30%">Responden</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($pencacah->kegiatan as $keg)
                                            <tr>
                                                <td>{{ $keg->nama_kegiatan }}</td>
                                                <td class="text-center">{{ $keg->jumlah_responden }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            @else
                                <span style="font-size: 11px; color: #6c757d;">Tidak ada kegiatan.</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="text-center">Data tidak ditemukan.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</body>
</html>