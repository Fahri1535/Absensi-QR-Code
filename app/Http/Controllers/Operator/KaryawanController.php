<?php

namespace App\Http\Controllers\Operator;

use App\Http\Controllers\Controller;
use App\Models\{Karyawan, User};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{DB, Hash, Storage};
use Illuminate\Support\Str;

class KaryawanController extends Controller
{
    /* ─── Index ──────────────────────────────────────────────── */

    public function index(Request $request)
    {
        $karyawanList = Karyawan::with('user')
            ->when($request->cari, fn($q, $s) => $q->where('nama_lengkap', 'like', "%{$s}%")
                ->orWhereHas('user', fn($u) => $u->where('username', 'like', "%{$s}%")))
            ->when($request->status, fn($q, $s) => $q->where('status', $s))
            ->orderBy('nama_lengkap')
            ->paginate(15)
            ->withQueryString();

        return view('operator.karyawan.index', compact('karyawanList'));
    }

    /* ─── Create / Store ─────────────────────────────────────── */

    public function create()
    {
        return view('operator.karyawan.index', ['showForm' => true]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'username'      => 'required|string|unique:users,username|max:50',
            'password'      => 'required|string|min:6',
            'nama_lengkap'  => 'required|string|max:100',
            'jabatan'       => 'nullable|string|max:100',
            'nomor_telepon' => 'nullable|string|max:20',
            'status'        => 'required|in:aktif,nonaktif',
            'foto'          => 'nullable|image|max:2048',
        ]);

        DB::transaction(function () use ($validated, $request) {
            $user = User::create([
                'username' => $validated['username'],
                'password' => Hash::make($validated['password']),
                'role'     => 'karyawan',
            ]);

            $fotoPath = null;
            if ($request->hasFile('foto')) {
                $fotoPath = $request->file('foto')->store("foto/{$user->id}", 'public');
            }

            Karyawan::create([
                'user_id'        => $user->id,
                'nama_lengkap'   => $validated['nama_lengkap'],
                'jabatan'        => $validated['jabatan'] ?? null,
                'nomor_telepon'  => $validated['nomor_telepon'] ?? null,
                'foto'           => $fotoPath,
                'status'         => $validated['status'],
                'kode_karyawan'  => 'KRY-' . Str::upper(Str::random(12)),
            ]);
        });

        return redirect()->route('operator.karyawan')->with('success', 'Karyawan berhasil ditambahkan.');
    }

    /* ─── Show ───────────────────────────────────────────────── */

    public function show(int $id)
    {
        $karyawan = Karyawan::with(['user', 'presensi' => fn($q) => $q->orderByDesc('tanggal')->take(10)])
            ->findOrFail($id);

        return view('operator.karyawan.index', ['karyawan' => $karyawan, 'showDetail' => true]);
    }

    /* ─── Edit / Update ──────────────────────────────────────── */

    public function edit(int $id)
    {
        $karyawan = Karyawan::with('user')->findOrFail($id);

        return view('operator.karyawan.index', ['karyawan' => $karyawan, 'showForm' => true]);
    }

    public function update(Request $request, int $id)
    {
        $karyawan = Karyawan::with('user')->findOrFail($id);

        $validated = $request->validate([
            'username'      => "required|string|unique:users,username,{$karyawan->user_id}|max:50",
            'password'      => 'nullable|string|min:6',
            'nama_lengkap'  => 'required|string|max:100',
            'jabatan'       => 'nullable|string|max:100',
            'nomor_telepon' => 'nullable|string|max:20',
            'status'        => 'required|in:aktif,nonaktif',
            'foto'          => 'nullable|image|max:2048',
        ]);

        DB::transaction(function () use ($validated, $request, $karyawan) {
            // Update user
            $userUpdate = ['username' => $validated['username']];
            if ($validated['password']) {
                $userUpdate['password'] = Hash::make($validated['password']);
            }
            $karyawan->user->update($userUpdate);

            // Update foto
            $fotoPath = $karyawan->foto;
            if ($request->hasFile('foto')) {
                if ($fotoPath) Storage::disk('public')->delete($fotoPath);
                $fotoPath = $request->file('foto')->store("foto/{$karyawan->user_id}", 'public');
            }

            $karyawan->update([
                'nama_lengkap'  => $validated['nama_lengkap'],
                'jabatan'       => $validated['jabatan'] ?? null,
                'nomor_telepon' => $validated['nomor_telepon'] ?? null,
                'status'        => $validated['status'],
                'foto'          => $fotoPath,
            ]);
        });

        return redirect()->route('operator.karyawan')->with('success', 'Data karyawan berhasil diperbarui.');
    }

    /* ─── Destroy ────────────────────────────────────────────── */

    public function destroy(int $id)
    {
        $karyawan = Karyawan::with('user')->findOrFail($id);

        if ($karyawan->foto) {
            Storage::disk('public')->delete($karyawan->foto);
        }

        $karyawan->user->delete(); // cascade ke karyawan

        return redirect()->route('operator.karyawan')->with('success', 'Karyawan berhasil dihapus.');
    }
}
