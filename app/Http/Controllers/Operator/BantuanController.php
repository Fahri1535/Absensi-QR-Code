<?php

namespace App\Http\Controllers\Operator;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\{Bantuan, BantuanSetting};

class BantuanController extends Controller
{
    public function index()
    {
        $bantuan = Bantuan::all();
        $jamOp = BantuanSetting::where('key', 'jam_operasional')->first()?->value ?? config('bantuan.jam_operasional');
        $sla = BantuanSetting::where('key', 'sla')->first()?->value ?? config('bantuan.sla');

        // Jika data kosong, masukkan data default dari config
        if ($bantuan->isEmpty()) {
            foreach (config('bantuan.kontak') as $slug => $data) {
                Bantuan::create(array_merge(['slug' => $slug], $data));
            }
            $bantuan = Bantuan::all();
        }

        return view('operator.bantuan.index', compact('bantuan', 'jamOp', 'sla'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'jam_operasional' => 'required|string',
            'sla'             => 'nullable|string',
            'kontak'          => 'required|array',
            'kontak.*.nama'   => 'required|string',
            'kontak.*.deskripsi' => 'nullable|string',
            'kontak.*.telepon' => 'nullable|string',
            'kontak.*.whatsapp' => 'nullable|string',
            'kontak.*.email' => 'nullable|email',
        ]);

        // Update settings
        BantuanSetting::updateOrCreate(['key' => 'jam_operasional'], ['value' => $request->jam_operasional]);
        BantuanSetting::updateOrCreate(['key' => 'sla'], ['value' => $request->sla]);

        // Update kontak
        foreach ($request->kontak as $slug => $data) {
            Bantuan::where('slug', $slug)->update($data);
        }

        return back()->with('success', 'Data bantuan berhasil diperbarui.');
    }
}
