<?php

namespace App\Http\Controllers\Hrd;

use App\Http\Controllers\Controller;
use App\Models\Karyawan;
use Illuminate\Http\Request;
use Illuminate\View\View;

class KaryawanController extends Controller
{
    public function index(Request $request): View
    {
        $karyawanList = Karyawan::query()
            ->with('user')
            ->whereHas('user', fn ($q) => $q->where('role', 'karyawan'))
            ->when($request->cari, fn ($q, $s) => $q->where('nama_lengkap', 'like', "%{$s}%")
                ->orWhereHas('user', fn ($u) => $u->where('username', 'like', "%{$s}%")))
            ->when($request->status, fn ($q, $s) => $q->where('status', $s))
            ->orderBy('nama_lengkap')
            ->paginate(15)
            ->withQueryString();

        return view('hrd.karyawan.index', compact('karyawanList'));
    }

    public function show(int $id): View
    {
        $karyawan = Karyawan::with([
            'user',
            'presensi' => fn ($q) => $q->orderByDesc('tanggal')->limit(30),
        ])->findOrFail($id);

        return view('hrd.karyawan.show', compact('karyawan'));
    }
}
