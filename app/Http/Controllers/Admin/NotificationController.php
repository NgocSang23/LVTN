<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index()
    {
        return response()->json(Notification::orderByDesc('created_at')->get());
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'message' => 'required|string',
        ]);

        $notification = Notification::create($validated);

        return response()->json([
            'message' => '✅ Đã gửi thông báo',
            'data' => $notification,
        ]);
    }

    public function destroy($id)
    {
        $notification = Notification::findOrFail($id);
        $notification->delete();

        return response()->json(['message' => '🗑️ Đã xoá thông báo']);
    }
}
