<?php

namespace App\Http\Controllers\Operator;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SetupController extends Controller
{
    public function index()
    {
        $dbConnection = config('database.default');
        $dbName = config("database.connections.{$dbConnection}.database");
        
        return view('operator.setup', compact('dbConnection', 'dbName'));
    }
}
