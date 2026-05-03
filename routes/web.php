<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\PresensiQrEntryController;
use App\Http\Controllers\{BantuanController, NotifikasiController};
use App\Http\Controllers\Karyawan\{
    DashboardController as KaryawanDashboard,
    PresensiController,
    RiwayatController,
    LaporanController as KaryawanLaporan,
    IzinController as KaryawanIzin,
    ProfilController,
};
use App\Http\Controllers\Operator\{
    DashboardController as OperatorDashboard,
    KaryawanController as OperatorKaryawan,
    QrcodeController,
    JadwalController,
    LaporanController as OperatorLaporan,
    PresensiController as OperatorPresensi,
    BantuanController as OperatorBantuan,
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
        Route::get('/presensi',            [PresensiController::class, 'index'])->name('presensi');
        Route::post('/presensi/scan',      [PresensiController::class, 'scan'])->name('presensi.scan');

        // Riwayat
        Route::get('/riwayat',             [RiwayatController::class, 'index'])->name('riwayat');
        Route::get('/riwayat/export',      [RiwayatController::class, 'export'])->name('riwayat.export');

        // Laporan
        Route::get('/laporan',             [KaryawanLaporan::class, 'index'])->name('laporan');

        // Izin
        Route::get('/izin',                [KaryawanIzin::class, 'index'])->name('izin');
        Route::post('/izin',               [KaryawanIzin::class, 'store'])->name('izin.store');
        Route::delete('/izin/{id}',        [KaryawanIzin::class, 'cancel'])->name('izin.cancel');

        // Profil
        Route::get('/profil',              [ProfilController::class, 'index'])->name('profil');
        Route::put('/profil',              [ProfilController::class, 'update'])->name('profil.update');
        Route::patch('/profil/password',   [ProfilController::class, 'updatePassword'])->name('password.update');
        Route::get('/qrcode/download',     [ProfilController::class, 'downloadQr'])->name('qrcode.download');
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
        Route::get('/qrcode',                     [QrcodeController::class, 'index'])->name('qrcode');
        Route::get('/qrcode/download',            [QrcodeController::class, 'download'])->name('qrcode.download');
        Route::get('/qrcode/print',               [QrcodeController::class, 'print'])->name('qrcode.print');
        Route::patch('/qrcode/toggle',            [QrcodeController::class, 'toggle'])->name('qrcode.toggle');

        // Jadwal
        Route::get('/jadwal',                     [JadwalController::class, 'index'])->name('jadwal');
        Route::patch('/jadwal',                   [JadwalController::class, 'update'])->name('jadwal.update');

        // Laporan
        Route::get('/laporan',                    [OperatorLaporan::class, 'index'])->name('laporan');
        Route::get('/laporan/export',             [OperatorLaporan::class, 'export'])->name('laporan.export');

        // Bantuan Management
        Route::get('/bantuan',                    [OperatorBantuan::class, 'index'])->name('bantuan.index');
        Route::patch('/bantuan',                  [OperatorBantuan::class, 'update'])->name('bantuan.update');
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
    });

/* ═══════════════════════════════════════════
   NOTIFIKASI (semua role yang sudah login)
═══════════════════════════════════════════ */
Route::middleware('auth')->group(function () {
    Route::get('/bantuan', BantuanController::class)->name('bantuan');

    Route::get('/notifikasi',                    [NotifikasiController::class, 'index'])->name('notifikasi');
    Route::patch('/notifikasi/{id}/baca',        [NotifikasiController::class, 'baca'])->name('notifikasi.baca');
    Route::post('/notifikasi/baca-semua',        [NotifikasiController::class, 'bacaSemua'])->name('notifikasi.baca-semua');
});