<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function destroy($id)
    {
        $notification = Notification::where('user_id', auth()->id())->findOrFail($id);
        $notification->delete();

        return back()->with('success', 'Đã xoá thông báo.');
    }

    public function deleteAll()
    {
         /** @var \App\Models\User $user */
         $user = auth()->user();

        $user->notifications()->delete();

        return back()->with('success', 'Đã xoá tất cả thông báo.');
    }
}
