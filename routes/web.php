<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\PresensiQrEntryController;
use App\Http\Controllers\{BantuanController, NotifikasiController, ProfileController, KaryawanProfileController};
use App\Http\Controllers\Karyawan\{
    DashboardController as KaryawanDashboard,
    PresensiController as KaryawanPresensi,
    RiwayatController as KaryawanRiwayat,
    LaporanController as KaryawanLaporan,
    IzinController as KaryawanIzin,
};
use App\Http\Controllers\Operator\{
    DashboardController as OperatorDashboard,
    PresensiController as OperatorPresensi,
    KaryawanController as OperatorKaryawan,
    JadwalController as OperatorJadwal,
    QrcodeController as OperatorQrCode,
    LaporanController as OperatorLaporan,
    BantuanController as OperatorBantuan,
    SetupController as OperatorSetup,
};
use App\Http\Controllers\Hrd\{
    DashboardController as HrdDashboard,
    IzinController as HrdIzin,
    KaryawanController as HrdKaryawan,
    LaporanController as HrdLaporan,
};

/* ═══════════════════════════════════════════
   REDIRECT ROOT
═══════════════════════════════════════════ */
Route::get('/', function () {
    if (auth()->check()) {
        return match(auth()->user()->role) {
            'karyawan' => redirect()->route('karyawan.dashboard'),
            'operator' => redirect()->route('operator.dashboard'),
            'hrd'      => redirect()->route('hrd.dashboard'),
            default    => redirect()->route('login'),
        };
    }
    return redirect()->route('login');
});

/* ═══════════════════════════════════════════
   AUTH
═══════════════════════════════════════════ */
Route::middleware('guest')->group(function () {
    Route::get('/login',  [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);
});

/* Kontak admin tanpa login (selaras dengan isi halaman /bantuan setelah masuk) */
Route::view('/kontak', 'kontak-publik')->name('kontak.publik');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout')->middleware('auth');

/* ═══════════════════════════════════════════
   PRESENSI QR — tautan publik (Google Lens / browser)
═══════════════════════════════════════════ */
Route::get('/presensi/scan', PresensiQrEntryController::class)->name('presensi.qr.entry');

/* ═══════════════════════════════════════════
   KARYAWAN
═══════════════════════════════════════════ */
Route::middleware(['auth', 'role:karyawan'])
    ->prefix('karyawan')
    ->name('karyawan.')
    ->group(function () {

        Route::get('/dashboard',           [KaryawanDashboard::class, 'index'])->name('dashboard');

        // Presensi
        Route::get('/presensi',            [KaryawanPresensi::class, 'index'])->name('presensi');
        Route::post('/presensi/scan',      [KaryawanPresensi::class, 'scan'])->name('presensi.scan');

        // Riwayat & Laporan (Digabung)
        Route::get('/riwayat',             [KaryawanRiwayat::class, 'index'])->name('riwayat');
        Route::get('/riwayat/export',      [KaryawanRiwayat::class, 'export'])->name('riwayat.export');

        // Izin
        Route::get('/izin',                [KaryawanIzin::class, 'index'])->name('izin');
        Route::post('/izin',               [KaryawanIzin::class, 'store'])->name('izin.store');
        Route::delete('/izin/{id}',        [KaryawanIzin::class, 'cancel'])->name('izin.cancel');

        // Profil
        Route::get('/profil',              [ProfileController::class, 'index'])->name('profil');
        Route::put('/profil',              [ProfileController::class, 'update'])->name('profil.update');
        Route::delete('/profil/foto',      [ProfileController::class, 'deleteFoto'])->name('profil.foto.delete');
        Route::patch('/profil/password',   [ProfileController::class, 'updatePassword'])->name('password.update');
        Route::get('/qrcode/download',     [ProfileController::class, 'downloadQr'])->name('qrcode.download');
    });

/* ═══════════════════════════════════════════
   OPERATOR
═══════════════════════════════════════════ */
Route::middleware(['auth', 'role:operator'])
    ->prefix('operator')
    ->name('operator.')
    ->group(function () {

        Route::get('/dashboard',                  [OperatorDashboard::class, 'index'])->name('dashboard');
        Route::get('/presensi', [OperatorPresensi::class, 'index'])->name('presensi');

        // Karyawan CRUD
        Route::get('/karyawan',                   [OperatorKaryawan::class, 'index'])->name('karyawan');
        Route::get('/karyawan/create',            [OperatorKaryawan::class, 'create'])->name('karyawan.create');
        Route::post('/karyawan',                  [OperatorKaryawan::class, 'store'])->name('karyawan.store');
        Route::get('/karyawan/{id}',              [OperatorKaryawan::class, 'show'])->name('karyawan.show');
        Route::get('/karyawan/{id}/edit',         [OperatorKaryawan::class, 'edit'])->name('karyawan.edit');
        Route::put('/karyawan/{id}',              [OperatorKaryawan::class, 'update'])->name('karyawan.update');
        Route::delete('/karyawan/{id}',           [OperatorKaryawan::class, 'destroy'])->name('karyawan.destroy');

        // QR Code
        Route::get('/qrcode',                     [OperatorQrCode::class, 'index'])->name('qrcode');
        Route::get('/qrcode/download',            [OperatorQrCode::class, 'download'])->name('qrcode.download');
        Route::get('/qrcode/print',               [OperatorQrCode::class, 'print'])->name('qrcode.print');
        Route::patch('/qrcode/toggle',            [OperatorQrCode::class, 'toggle'])->name('qrcode.toggle');

        // Jadwal
        Route::get('/jadwal',                     [OperatorJadwal::class, 'index'])->name('jadwal');
        Route::patch('/jadwal',                   [OperatorJadwal::class, 'update'])->name('jadwal.update');

        // Laporan
        Route::get('/laporan',                    [OperatorLaporan::class, 'index'])->name('laporan');
        Route::get('/laporan/export',             [OperatorLaporan::class, 'export'])->name('laporan.export');

        // Bantuan Management
        Route::get('/bantuan',                    [OperatorBantuan::class, 'index'])->name('bantuan.index');
        Route::patch('/bantuan',                  [OperatorBantuan::class, 'update'])->name('bantuan.update');

        // Panduan Instalasi & Database
        Route::get('/setup',                      [OperatorSetup::class, 'index'])->name('setup');

        // Profil
        Route::get('/profil',                     [ProfileController::class, 'index'])->name('profil');
        Route::put('/profil',                     [ProfileController::class, 'update'])->name('profil.update');
        Route::delete('/profil/foto',             [ProfileController::class, 'deleteFoto'])->name('profil.foto.delete');
        Route::patch('/profil/password',          [ProfileController::class, 'updatePassword'])->name('password.update');
    });

/* ═══════════════════════════════════════════
   HRD
═══════════════════════════════════════════ */
Route::middleware(['auth', 'role:hrd'])
    ->prefix('hrd')
    ->name('hrd.')
    ->group(function () {

        Route::get('/dashboard',              [HrdDashboard::class, 'index'])->name('dashboard');

        // Data karyawan (baca + detail)
        Route::get('/karyawan',               [HrdKaryawan::class, 'index'])->name('karyawan');
        Route::get('/karyawan/{id}',          [HrdKaryawan::class, 'show'])->name('karyawan.show');

        // Izin
        Route::get('/izin',                   [HrdIzin::class, 'index'])->name('izin');
        Route::patch('/izin/{id}',            [HrdIzin::class, 'approve'])->name('izin.approve');

        // Laporan
        Route::get('/laporan',                [HrdLaporan::class, 'index'])->name('laporan');
        Route::get('/laporan/export',         [HrdLaporan::class, 'export'])->name('laporan.export');

        // Fitur Presensi Pribadi untuk HRD (Sama seperti Karyawan)
        Route::get('/presensi-pribadi',       [KaryawanPresensi::class, 'index'])->name('presensi');
        Route::post('/presensi-pribadi/scan', [KaryawanPresensi::class, 'scan'])->name('presensi.scan');
        Route::get('/riwayat-pribadi',        [KaryawanRiwayat::class, 'index'])->name('riwayat');
        Route::get('/riwayat-pribadi/export', [KaryawanRiwayat::class, 'export'])->name('riwayat.export');
        
        // Izin Pribadi
        Route::get('/izin-pribadi',           [KaryawanIzin::class, 'index'])->name('izin_pribadi');
        Route::post('/izin-pribadi',          [KaryawanIzin::class, 'store'])->name('izin_pribadi.store');
        Route::delete('/izin-pribadi/{id}',   [KaryawanIzin::class, 'cancel'])->name('izin_pribadi.cancel');

        // Profil
        Route::get('/profil',                 [ProfileController::class, 'index'])->name('profil');
        Route::put('/profil',                 [ProfileController::class, 'update'])->name('profil.update');
        Route::delete('/profil/foto',         [ProfileController::class, 'deleteFoto'])->name('profil.foto.delete');
        Route::patch('/profil/password',      [ProfileController::class, 'updatePassword'])->name('password.update');
        Route::get('/qrcode/download',        [ProfileController::class, 'downloadQr'])->name('qrcode.download');
    });

/* ═══════════════════════════════════════════
   NOTIFIKASI (semua role yang sudah login)
═══════════════════════════════════════════ */
Route::get('/karyawan/{kode_karyawan}', [KaryawanProfileController::class, 'show'])->name('karyawan.profile-public');

Route::middleware('auth')->group(function () {
    Route::get('/bantuan', BantuanController::class)->name('bantuan');

    Route::get('/notifikasi',                    [NotifikasiController::class, 'index'])->name('notifikasi');
    Route::patch('/notifikasi/{id}/baca',        [NotifikasiController::class, 'baca'])->name('notifikasi.baca');
    Route::post('/notifikasi/baca-semua',        [NotifikasiController::class, 'bacaSemua'])->name('notifikasi.baca-semua');
});