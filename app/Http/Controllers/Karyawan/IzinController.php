<?php

namespace App\Http\Controllers\Karyawan;

use App\Http\Controllers\Controller;
use App\Models\{Izin, Notifikasi, User};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class IzinController extends Controller
{
    public function index(Request $request)
    {
        $karyawan = auth()->user()->karyawan;
        $izinList = Izin::where('karyawan_id', $karyawan->id)
            ->when($request->status, fn($q, $s) => $q->where('status', $s))
            ->orderByDesc('created_at')
            ->paginate(10);

        $stats = [
            'pending'   => Izin::where('karyawan_id', $karyawan->id)->where('status', 'pending')->count(),
            'disetujui' => Izin::where('karyawan_id', $karyawan->id)->where('status', 'disetujui')->count(),
            'ditolak'   => Izin::where('karyawan_id', $karyawan->id)->where('status', 'ditolak')->count(),
        ];

        return view('karyawan.izin', compact('karyawan', 'izinList', 'stats'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'jenis_izin'      => 'required|in:izin,sakit,cuti,tugas_luar,alpa',
            'tanggal_mulai'   => 'required|date|after_or_equal:today',
            'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
            'keterangan'      => 'nullable|string|max:1000',
            'lampiran'        => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);

        $karyawan  = auth()->user()->karyawan;
        $lampiranPath = null;

        if ($request->hasFile('lampiran')) {
            $lampiranPath = $request->file('lampiran')
                ->store("izin/{$karyawan->id}", 'public');
        }

        $izin = Izin::create([
            'karyawan_id'     => $karyawan->id,
            'jenis_izin'      => $validated['jenis_izin'],
            'tanggal_mulai'   => $validated['tanggal_mulai'],
            'tanggal_selesai' => $validated['tanggal_selesai'],
            'keterangan'      => $validated['keterangan'] ?? null,
            'lampiran'        => $lampiranPath,
            'status'          => 'pending',
        ]);

        // Notifikasi ke semua HRD
        $hrds = User::where('role', 'hrd')->get();
        foreach ($hrds as $hrd) {
            Notifikasi::create([
                'user_id' => $hrd->id,
                'judul'   => 'Pengajuan Izin Baru',
                'pesan'   => "{$karyawan->nama_lengkap} mengajukan " . ucfirst($izin->jenis_izin) . " · {$izin->tanggal_mulai->format('d M Y')}",
                'ikon'    => 'fa-file-circle-plus',
                'warna'   => 'amber',
                'link'    => route('hrd.izin'),
            ]);
        }

        return back()->with('success', 'Pengajuan izin berhasil dikirim.');
    }

    public function cancel(int $id)
    {
        $karyawan = auth()->user()->karyawan;
        $izin     = Izin::where('karyawan_id', $karyawan->id)
            ->where('status', 'pending')
            ->findOrFail($id);

        if ($izin->lampiran) {
            Storage::disk('public')->delete($izin->lampiran);
        }

        $izin->delete();

        return back()->with('success', 'Pengajuan izin dibatalkan.');
    }
}
