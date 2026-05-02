<?php

namespace App\Http\Controllers\Hrd;

use App\Http\Controllers\Controller;
use App\Models\{Izin, Notifikasi};
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class IzinController extends Controller
{
    public function index(Request $request)
    {
        $status  = $request->input('status', 'pending');
        $showAll = $status === 'semua';

        $izinList = Izin::with('karyawan')
            ->when(! $showAll, fn($q) => $q->where('status', $status))
            ->orderByDesc('created_at')
            ->paginate(15)
            ->withQueryString();

        $countPending = Izin::where('status', 'pending')->count();

        return view('hrd.izin', compact('izinList', 'countPending'));
    }

    /** Setujui atau tolak */
    public function approve(Request $request, int $id)
    {
        // FIXED: field 'aksi' → 'status' (sesuai input hidden di view hrd/dashboard.blade)
        $validated = $request->validate([
            'status'      => 'required|in:disetujui,ditolak',
            'catatan_hrd' => 'nullable|string|max:500',
        ]);

        $izin = Izin::with('karyawan.user')->findOrFail($id);

        if ($izin->status !== 'pending') {
            return back()->withErrors(['msg' => 'Izin sudah diproses.']);
        }

        $izin->update([
            'status'      => $validated['status'],
            'catatan_hrd' => $validated['catatan_hrd'] ?? null,
            // FIXED: approved_by → auth()->id() (primary key user)
            'approved_by' => auth()->user()->id,
            'approved_at' => now(),
        ]);

        // FIXED: user_id field di notifikasi = karyawan->user_id (bukan karyawan->user->id yang bisa null)
        $statusLabel = $validated['status'] === 'disetujui' ? 'disetujui ✅' : 'ditolak ❌';

        if ($izin->karyawan && $izin->karyawan->user_id) {
            $jenisLabel = ucfirst(str_replace('_', ' ', $izin->jenis_izin));
            Notifikasi::create([
                'user_id' => $izin->karyawan->user_id,
                'judul'   => 'Status Izin: ' . ($validated['status'] === 'disetujui' ? 'Disetujui' : 'Ditolak'),
                'pesan'   => "Pengajuan {$jenisLabel} ({$izin->tanggal_mulai->format('d M')} – {$izin->tanggal_selesai->format('d M Y')}) telah {$statusLabel}."
                    . (! empty($validated['catatan_hrd']) ? ' Catatan HRD: ' . Str::limit((string) $validated['catatan_hrd'], 160) : ''),
                'ikon'    => $validated['status'] === 'disetujui' ? 'fa-circle-check' : 'fa-circle-xmark',
                'warna'   => $validated['status'] === 'disetujui' ? 'green' : 'red',
                'link'    => route('karyawan.izin'),
            ]);
        }

        return back()->with('success', "Izin berhasil {$statusLabel}.");
    }
}