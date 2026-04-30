<?php

namespace App\Http\Controllers\Operator;

use App\Http\Controllers\Controller;
use App\Models\{JadwalKerja, QrCode};
use Illuminate\Http\Request;
use SimpleSoftwareIO\QrCode\Facades\QrCode as QrFacade;

class QrcodeController extends Controller
{
    public function index()
    {
        // FIXED: ::getSetting() bukan ::get()
        $jadwal   = JadwalKerja::getSetting();
        $qrMasuk  = QrCode::getOrCreate('masuk');
        $qrPulang = QrCode::getOrCreate('pulang');

        // Generate gambar SVG inline
        $qrMasukImage  = QrFacade::size(180)->generate($qrMasuk->kode_qr);
        $qrPulangImage = QrFacade::size(180)->generate($qrPulang->kode_qr);

        return view('operator.qrcode', compact(
            'jadwal', 'qrMasuk', 'qrPulang', 'qrMasukImage', 'qrPulangImage'
        ));
    }

    /** Download QR sebagai PNG */
    public function download(Request $request)
    {
        $tipe = $request->input('type', 'masuk');
        $qr   = QrCode::getOrCreate($tipe);

        $image = QrFacade::format('png')->size(600)->errorCorrection('H')->generate($qr->kode_qr);

        return response($image, 200, [
            'Content-Type'        => 'image/png',
            'Content-Disposition' => "attachment; filename=\"qr-presensi-{$tipe}.png\"",
        ]);
    }

    /** Print view */
    public function print(Request $request)
    {
        $tipe  = $request->input('type', 'masuk');
        $qr    = QrCode::getOrCreate($tipe);
        // FIXED: tambah ::getSetting() untuk jadwal di print view
        $jadwal = JadwalKerja::getSetting();
        $image = QrFacade::size(400)->generate($qr->kode_qr);

        return view('operator.qrcode_print', compact('tipe', 'image', 'qr', 'jadwal'));
    }

    /** Toggle aktif/nonaktif */
    public function toggle(Request $request)
    {
        $tipe = $request->input('type', 'masuk');
        $qr   = QrCode::getOrCreate($tipe);
        $qr->update(['is_active' => ! $qr->is_active]);

        $label = $qr->is_active ? 'diaktifkan' : 'dinonaktifkan';

        return back()->with('success', "QR Code presensi {$tipe} berhasil {$label}.");
    }
}