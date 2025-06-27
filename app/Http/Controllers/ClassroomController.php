<?php

namespace App\Http\Controllers;

use App\Models\ClassRoom;
use App\Models\ClassroomUser;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ClassroomController extends Controller
{
    /**
     * 1. Hiển thị danh sách lớp học mà giáo viên đã tạo
     * - Chỉ lấy các lớp mà teacher_id = id của người đang đăng nhập
     */
    public function index()
    {
        $classrooms = Classroom::withCount('users')->where('teacher_id', auth()->id())->get();

        return view('user.classrooms.index', compact('classrooms'));
    }

    /**
     * 2. Hiển thị form tạo lớp học (GET)
     * - Chuyển hướng sang view có form nhập tên, mô tả lớp
     */
    public function create()
    {
        return view('user.classrooms.create');
    }

    /**
     * 3. Lưu lớp học mới sau khi người dùng gửi form (POST)
     * - Validate đầu vào
     * - Tạo mã lớp ngẫu nhiên (code) đảm bảo không bị trùng
     * - Lưu vào bảng classrooms
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        // Tạo mã lớp không trùng (6 ký tự in hoa)
        do {
            $code = strtoupper(Str::random(6));
        } while (ClassRoom::where('code', $code)->exists());

        ClassRoom::create([
            'name' => $request->name,
            'description' => $request->description,
            'code' => $code,
            'teacher_id' => auth()->id(), // ID của giáo viên tạo lớp
        ]);

        return redirect()->route('classrooms.index')->with('success', 'Tạo lớp học thành công!');
    }

    public function show($id)
    {
        $classroom = ClassRoom::with('members')->findOrFail($id);

        // Nếu là học viên (không phải giáo viên)
        if (auth()->user()->roles !== 'teacher') {
            $isMember = $classroom->users->contains(auth()->id());

            if (! $isMember) {
                return redirect()->route('classrooms.my')->with('error', 'Bạn đã bị xoá khỏi lớp học này.');
            }
        }

        return view('user.classrooms.show', compact('classroom'));
    }


    public function leave($id)
    {
        $classroom = ClassRoom::findOrFail($id);
        $classroom->users()->detach(auth()->id());
        return redirect()->route('user.classrooms.my')->with('success', 'Bạn đã rời lớp học thành công!');
    }

    public function removeStudent($classroomId, $userId)
    {
        $classroom = ClassRoom::findOrFail($classroomId);

        if ($classroom->teacher_id != auth()->id()) {
            abort(403);
        }

        $classroom->users()->detach($userId);

        $user = User::find($userId);

        Notification::create([
            'user_id' => $userId,
            'title' => 'Bạn đã bị xóa khỏi lớp',
            'message' => "Bạn đã bị xóa khỏi lớp học {$classroom->name} bởi giáo viên.",
        ]);

        return back()->with('success', 'Xoá học viên khỏi lớp thành công!');
    }

    public function inviteLink($code)
    {
        $request = new \Illuminate\Http\Request(['code' => $code]);
        return $this->joinByCode($request);
    }

    /**
     * 4. Hiển thị form để học viên nhập mã lớp và tham gia (GET)
     * - View gồm 1 input để nhập mã lớp
     */
    public function joinForm()
    {
        return view('user.classrooms_user.join'); // cần tạo view này
    }

    /**
     * 5. Xử lý học viên tham gia lớp bằng mã (POST)
     * - Tìm lớp theo mã
     * - Kiểm tra người dùng đã tham gia chưa
     * - Nếu chưa thì thêm vào bảng classroom_users
     */
    public function joinByCode(Request $request)
    {
        $request->validate(['code' => 'required']);

        $classroom = Classroom::where('code', $request->code)->first();

        // Nếu không có lớp học tương ứng
        if (!$classroom) {
            return back()->withErrors(['code' => 'Mã lớp không tồn tại.']);
        }

        // Kiểm tra xem user đã trong lớp này chưa
        $alreadyJoined = ClassroomUser::where('classroom_id', $classroom->id)
            ->where('user_id', auth()->id())
            ->exists();

        if ($alreadyJoined) {
            return back()->with('info', 'Bạn đã tham gia lớp này rồi.');
        }

        // Nếu chưa thì thêm vào bảng classroom_users
        ClassroomUser::create([
            'classroom_id' => $classroom->id,
            'user_id' => auth()->id(),
        ]);

        return redirect()->route('classrooms.my')->with('success', 'Tham gia lớp học thành công!');
    }

    /**
     * 6. Hiển thị danh sách lớp học mà học viên đã tham gia
     * - Lấy từ quan hệ many-to-many giữa User và ClassRoom
     * - Cần định nghĩa hàm joinedClassrooms trong model User
     */
    public function myClassrooms()
    {
        $classrooms = auth()->user()->joinedClassrooms; // ->belongsToMany ở User
        return view('user.classrooms_user.my', compact('classrooms'));
    }
}
