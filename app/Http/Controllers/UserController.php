<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use App\Models\Card;
use App\Models\ClassRoom;
use App\Models\Test;

class UserController extends Controller
{
    public function dashboard(Request $request)
    {
        $card_defines = Card::with(['question.topic.subject', 'user'])
            ->whereHas('question', function ($query) {
                $query->where('type', 'definition');
            })
            ->latest()
            ->get()
            ->filter(fn($card) => $card->question && $card->question->topic)
            ->groupBy(fn($card) => $card->question->topic->id)
            ->map(fn($group) => [
                'first_card' => $group->first(), // Card đầu tiên hiển thị
                'card_ids' => $group->pluck('id')->implode(','), // ID của tất cả cards cùng chủ đề
                'encoded_ids' => base64_encode($group->pluck('id')->implode(',')), // 👈 thêm dòng này
            ])
            ->take(6);

        $tests = Test::with(['questionnumbers.topic', 'user'])->latest()->get()->take(6);

        $myClassrooms = [];
        if (auth()->check() && auth()->user()->roles === 'teacher') {
            $myClassrooms = ClassRoom::where('teacher_id', auth()->id())->get();
        }

        return view('user.dashboard', compact('card_defines', 'tests', 'myClassrooms'));
    }

    public function library(Request $request)
    {
        $tab = $request->get('tab', 'define_essay');

        $card_defines = collect();
        $card_essays = collect();
        $tests = collect();
        $myClassrooms = collect();

        if ($tab === 'define_essay') {
            $card_defines = Card::with(['question.topic.subject', 'user'])
                ->where('user_id', Auth::id())
                ->whereHas('question', fn($q) => $q->where('type', 'definition'))
                ->latest()->get()
                ->filter(fn($card) => $card->question && $card->question->topic)
                ->groupBy(fn($card) => $card->question->topic->id)
                ->map(fn($group) => [
                    'first_card' => $group->first(),
                    'card_ids' => $group->pluck('id')->implode(','),
                ])->take(6);

            $card_essays = Card::with(['question.topic.subject', 'user'])
                ->where('user_id', Auth::id())
                ->whereHas('question', fn($q) => $q->where('type', 'essay'))
                ->latest()->get()
                ->filter(fn($card) => $card->question && $card->question->topic)
                ->groupBy(fn($card) => $card->question->topic->id)
                ->map(fn($group) => [
                    'first_card' => $group->first(),
                    'card_ids' => $group->pluck('id')->implode(','),
                ])->take(6);
        }

        if ($tab === 'multiple') {
            $tests = Test::with(['questionnumbers.topic', 'user'])
                ->where('user_id', Auth::id())
                ->latest()
                ->take(6)
                ->get();

            if (auth()->user()->roles === 'teacher') {
                $myClassrooms = ClassRoom::where('teacher_id', Auth::id())->get();
            }
        }

        return view('user.library.index', compact('tab', 'card_defines', 'card_essays', 'tests', 'myClassrooms'));
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('user.login');
    }

    public function login()
    {
        return view('user.login');
    }

    public function register()
    {
        return view('user.register');
    }

    public function profile()
    {
        $user = User::find(Auth::guard('web')->user()->id);
        return view('user.profile', compact('user'));
    }

    public function post_register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            // Tạo một đối tượng Validator::make, $request->all() lấy tất cả dữ liệu được gửi từ form
            'name' => 'required|max:255',
            'email' => 'required|email|max:255|unique:users',
            'username' => 'required|max:100|unique:users',
            'password' => 'required|confirmed|min:8',
        ], [
            'name.required' => 'Không được bỏ trống ô này',
            'email.required' => 'Không được bỏ trống ô này',
            'email.unique' => 'Email này đã tồn tại',
            'username.required' => 'Không được bỏ trống ô này',
            'username.unique' => 'Tên người dùng đã tồn tại',
            'password.required' => 'Không được bỏ trống ô này',
            'password.confirmed' => 'Mật khẩu không khớp',
            'password.min' => 'Mật khẩu phải ít nhất 8 ký tự'
        ]);

        if ($validator->fails()) { // Nếu có lỗi xảy ra thì sẻ trả về trang đăng ký người dùng
            return redirect()->back()->withErrors($validator)->withInput();
            // withInput gửi kèm dữ liệu người dùng đã nhập, khỏi nhập lại
        }

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'username' => $request->username,
            'password' => Hash::make($request->password),
            'roles' => $request->roles
        ]);

        return redirect()->route('user.login')->with('success', 'Đăng ký thành công! Vui lòng đăng nhập.');
    }

    public function post_login(Request $request)
    {
        $request->validate([
            'login' => 'required',
            'password' => 'required'
        ], [
            'login.required' => 'Username hoặc Email không đúng',
            'password.required' => 'Mật khẩu không đúng'
        ]);

        $login = $request->input('login');
        $password = $request->input('password');

        $user = User::where('username', $login)->orWhere('email', $login)->first();

        if (!$user || !Hash::check($password, $user->password)) {
            return redirect()->back()->withErrors(['login' => 'Tên đăng nhập hoặc mật khẩu không chính xác.']);
        }

        // Đăng nhập người dùng và chuyển hướng về dashboard, đồng thời lưu thông tin của user vào session
        auth()->login($user, $request->has('remember'));

        return redirect()->route('user.dashboard')->with('success', 'Đăng nhập thành công!');
    }

    public function update_profile(Request $request)
    {
        $validator = Validator::make($request->all(), [
            // Tạo một đối tượng Validator::make, $request->all() lấy tất cả dữ liệu được gửi từ form
            'name' => 'required|max:255',
            'email' => 'required|email|max:255',
            'username' => 'required|max:100',
            'password' => 'nullable|min:8',
        ], [
            'name.required' => 'Không được bỏ trống ô này',
            'email.required' => 'Không được bỏ trống ô này',
            'username.required' => 'Không được bỏ trống ô này',
            'password.required' => 'Không được bỏ trống ô này',
            'password.min' => 'Mật khẩu phải ít nhất 8 ký tự'
        ]);

        if ($validator->fails()) { // Nếu có lỗi xảy ra thì sẻ trả về trang đăng ký người dùng
            return redirect()->back()->withErrors($validator)->withInput();
            // withInput gửi kèm dữ liệu người dùng đã nhập, khỏi nhập lại
        }

        $user = User::find(auth()->id());

        $user->update([
            'name' => $request->name,
            'email' => $request->email,
            'username' => $request->username,
            'password' => $request->filled('password') ? Hash::make($request->password) : $user->password
        ]);

        return redirect()->back()->with('success', 'Cập nhật thông tin thành công!');
    }

    public function notifications()
    {
        /** @var \App\Models\User $user */
        $user = auth()->user();
        $notifications = $user->notifications()->latest()->paginate(10);
        $user->notifications()->update(['is_read' => true]);

        return view('user.notifications.index', compact('notifications'));
    }
}
