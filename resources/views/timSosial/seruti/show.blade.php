@extends('layouts.app')
@section('title', 'Detail Seruti')
@section('header-title', 'Seruti - Detail')

@section('content')
    <div class="p-4 md:p-6">
        <div class="bg-white shadow rounded-xl p-5">
            <dl class="grid md:grid-cols-2 gap-x-6 gap-y-3 text-sm">
                <div>
                    <dt class="text-gray-500">Nama Kegiatan</dt>
                    <dd class="font-medium">{{ $seruti->nama_kegiatan }}</dd>
                </div>
                <div>
                    <dt class="text-gray-500">Blok Sensus/Responden</dt>
                    <dd class="font-medium">{{ $seruti->BS_Responden ?: '-' }}</dd>
                </div>
                <div>
                    <dt class="text-gray-500">Pencacah</dt>
                    <dd class="font-medium">{{ $seruti->pencacah }}</dd>
                </div>
                <div>
                    <dt class="text-gray-500">Pengawas</dt>
                    <dd class="font-medium">{{ $seruti->pengawas }}</dd>
                </div>
                <div>
                    <dt class="text-gray-500">Target Penyelesaian</dt>
                    <dd class="font-medium">
                        {{ $seruti->target_penyelesaian ? \Carbon\Carbon::parse($seruti->target_penyelesaian)->format('d/m/Y') : '-' }}
                    </dd>
                </div>
                <div>
                    <dt class="text-gray-500">Flag Progress</dt>
                    <dd class="font-medium">{{ $seruti->flag_progress }}</dd>
                </div>
                <div class="md:col-span-2">
                    <dt class="text-gray-500">Tanggal Pengumpulan</dt>
                    <dd class="font-medium">
                        {{ $seruti->tanggal_pengumpulan ? \Carbon\Carbon::parse($seruti->tanggal_pengumpulan)->format('Y-m-d H:i') : '-' }}
                    </dd>
                </div>
            </dl>

            <div class="mt-5 flex gap-2">
                <a class="px-4 py-2 rounded bg-blue-600 text-white"
                    href="{{ route('sosial.seruti.edit', $seruti) }}">Edit</a>

                <form action="{{ route('sosial.seruti.destroy', $seruti) }}" method="post"
                    onsubmit="return confirm('Hapus data ini?')">
                    @csrf @method('DELETE')
                    <button class="px-4 py-2 rounded border border-rose-600 text-rose-600">Delete</button>
                </form>

                <a class="px-4 py-2 rounded border ms-auto"
                    href="{{ route('sosial.seruti.index', ['tw' => preg_match('/Seruti\-(TW[1-4])/', $seruti->nama_kegiatan, $m) ? $m[1] : 'TW1']) }}">
                    Kembali
                </a>
            </div>
        </div>
    </div>
@endsection
