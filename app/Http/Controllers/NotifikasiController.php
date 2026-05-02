<?php

namespace App\Http\Controllers;

use App\Models\Notifikasi;
use Illuminate\Http\Request;

class NotifikasiController extends Controller
{
    public function index()
    {
        $notifikasi = Notifikasi::where('user_id', auth()->id())
            ->orderByDesc('created_at')
            ->paginate(20);

        $unreadCount = Notifikasi::where('user_id', auth()->id())
            ->where('is_read', false)
            ->count();

        return view('notifikasi', compact('notifikasi', 'unreadCount'));
    }

    public function baca(int $id)
    {
        Notifikasi::where('user_id', auth()->id())
            ->findOrFail($id)
            ->update(['is_read' => true]);

        return back();
    }

    public function bacaSemua(Request $request)
    {
        Notifikasi::where('user_id', auth()->id())
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return back()->with('success', 'Semua notifikasi ditandai sudah dibaca.');
    }
}
