<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

use App\Models\{Bantuan, BantuanSetting};

class BantuanController extends Controller
{
    public function __invoke(): View
    {
        $bantuan = Bantuan::all();
        $jamOp = BantuanSetting::where('key', 'jam_operasional')->first()?->value ?? config('bantuan.jam_operasional');
        $sla = BantuanSetting::where('key', 'sla')->first()?->value ?? config('bantuan.sla');

        // Jika data kosong di DB (belum pernah di-input operator), gunakan data config
        if ($bantuan->isEmpty()) {
            $kontakData = config('bantuan.kontak');
            // Kita ubah ke format collection agar view tidak berubah banyak
            $kontaks = collect($kontakData);
        } else {
            // Ubah dari collection models ke format array yang diharapkan view partials
            $kontaks = $bantuan->keyBy('slug')->map(function($item) {
                return [
                    'nama' => $item->nama,
                    'deskripsi' => $item->deskripsi,
                    'telepon' => $item->telepon,
                    'whatsapp' => $item->whatsapp,
                    'email' => $item->email,
                ];
            });
        }

        return view('bantuan', compact('kontaks', 'jamOp', 'sla'));
    }
}
