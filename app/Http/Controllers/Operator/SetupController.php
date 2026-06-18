<?php

namespace App\Http\Controllers\Operator;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Presensi;

class SetupController extends Controller
{
    public function index()
    {
        $dbConnection = config('database.default');
        $dbName = config("database.connections.{$dbConnection}.database");
        $presensiCount = Presensi::count();
        
        return view('operator.setup', compact('dbConnection', 'dbName', 'presensiCount'));
    }

    public function deletePresensi()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        Presensi::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1');
        
        return back()->with('success', 'Semua data riwayat presensi berhasil dihapus!');
    }
}
