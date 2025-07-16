<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        // Lấy các thông báo do chính admin gửi
        $notifications = Notification::where(function ($q) use ($request) {
            $q->whereNull('user_id')
                ->orWhere('user_id', $request->user()->id);
        })
            ->orderByDesc('created_at')
            ->get();

        return response()->json($notifications);
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'message' => 'required|string',
        ]);

        $users = User::all(); // hoặc lọc theo điều kiện nếu cần

        foreach ($users as $user) {
            Notification::create([
                'user_id' => $user->id,
                'title' => $request->title,
                'message' => $request->message,
                'url' => null,
                'is_read' => false,
            ]);
        }

        return response()->json(['message' => '✅ Đã gửi thông báo đến tất cả người dùng']);
    }

    // app/Http/Controllers/Admin/NotificationController.php

    public function destroy($id)
    {
        $notification = Notification::where('id', $id)
            ->where('user_id', auth()->id()) // đảm bảo chỉ xoá thông báo của chính mình
            ->firstOrFail();

        $notification->delete();

        return response()->json(['message' => '🗑️ Đã xoá thông báo']);
    }

    public function destroyAll()
    {
        Notification::where('user_id', auth()->id())->delete();

        return response()->json(['message' => '🗑️ Đã xoá tất cả thông báo']);
    }
}
