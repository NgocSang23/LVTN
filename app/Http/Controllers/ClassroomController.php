<?php

namespace App\Http\Controllers;

use App\Exports\ClassResultExport; // Import lớp để xuất Excel
use App\Models\ClassRoom; // Import Model ClassRoom (đại diện bảng `class_rooms`)
use App\Models\ClassroomUser; // Import Model ClassroomUser (đại diện bảng `classroom_users`, bảng trung gian cho quan hệ nhiều-nhiều)
use App\Models\History; // Import Model History (đại diện bảng `histories`, lưu lịch sử làm bài)
use App\Models\Notification; // Import Model Notification (đại diện bảng `notifications`, lưu thông báo)
use App\Models\User; // Import Model User (đại diện bảng `users`)
use Illuminate\Http\Request; // Import Request để xử lý dữ liệu từ form
use Illuminate\Support\Facades\DB; // Import DB facade để thực hiện các truy vấn cơ sở dữ liệu phức tạp hơn
use Illuminate\Support\Str; // Import Str để thao tác với chuỗi (tạo mã ngẫu nhiên)
use Maatwebsite\Excel\Facades\Excel; // Import Excel facade để xuất file Excel

class ClassroomController extends Controller
{
    /**
     * 1. Hiển thị danh sách lớp học mà giáo viên đã tạo
     * - Chỉ lấy các lớp mà teacher_id = id của người đang đăng nhập
     */
    public function index(Request $request)
    {
        $query = Classroom::withCount('users')
            ->where('teacher_id', auth()->id());

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $classrooms = $query->get();

        return view('user.classrooms.index', compact('classrooms'));
    }

    /**
     * 2. Hiển thị form tạo lớp học (GET)
     * - Chuyển hướng sang view có form nhập tên, mô tả lớp
     */
    public function create()
    {
        // Trả về view để hiển thị form tạo lớp học mới.
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
        // Validate dữ liệu từ request. Đảm bảo 'name' là bắt buộc, chuỗi, tối đa 255 ký tự. 'description' là chuỗi và có thể rỗng.
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        // Tạo mã lớp không trùng (6 ký tự in hoa)
        do {
            $code = strtoupper(Str::random(6)); // Tạo chuỗi ngẫu nhiên 6 ký tự và chuyển thành chữ hoa.
            // TRUY VẤN: Kiểm tra xem mã lớp đã tồn tại chưa
            // ClassRoom::where('code', $code)->exists() : Kiểm tra xem có bản ghi ClassRoom nào có 'code' trùng với mã vừa tạo hay không. Trả về true nếu có, false nếu không.
        } while (ClassRoom::where('code', $code)->exists()); // Lặp lại cho đến khi tạo được một mã 'code' duy nhất.

        // TRUY VẤN: Tạo một lớp học mới và lưu vào cơ sở dữ liệu
        // ClassRoom::create([...]) : Tạo một bản ghi mới trong bảng 'class_rooms' với các dữ liệu được cung cấp.
        ClassRoom::create([
            'name' => $request->name, // Tên lớp từ request
            'description' => $request->description, // Mô tả lớp từ request
            'code' => $code, // Mã lớp duy nhất vừa tạo
            'teacher_id' => auth()->id(), // ID của giáo viên hiện tại (người đang đăng nhập) là người tạo lớp.
        ]);

        // Chuyển hướng về trang danh sách lớp học với thông báo thành công.
        return redirect()->route('classrooms.index')->with('success', 'Tạo lớp học thành công!');
    }

    public function update(Request $request, $id)
    {
        $classroom = Classroom::findOrFail($id);
        $classroom->update($request->only('name', 'description'));
        return redirect()->route('classrooms.index')->with('success', 'Cập nhật thành công.');
    }

    public function destroy($id)
    {
        $classroom = ClassRoom::findOrFail($id);

        // Lấy danh sách tất cả học viên trong lớp
        $studentIds = $classroom->users()->pluck('users.id')->toArray();

        // Gửi thông báo đến từng học viên
        foreach ($studentIds as $studentId) {
            Notification::create([
                'user_id' => $studentId,
                'title' => 'Lớp học đã bị xoá',
                'message' => "Lớp học '{$classroom->name}' mà bạn tham gia đã bị xoá bởi giáo viên.",
            ]);
        }

        // Xoá các bản ghi liên kết
        $classroom->classroomUsers()->delete();         // Xoá học viên khỏi lớp
        $classroom->sharedFlashcards()->delete();       // Xoá flashcard chia sẻ
        $classroom->tests()->detach();                  // Xoá trong bảng pivot classroom_tests (nếu có)

        // Cuối cùng xoá lớp học
        $classroom->delete();

        return redirect()->route('classrooms.index')->with('success', 'Đã xoá lớp học và thông báo đến học viên.');
    }

    public function show($id)
    {
        // TRUY VẤN: Tìm lớp học theo ID và tải kèm các quan hệ liên quan
        // ClassRoom::with([...]) : Tải các quan hệ 'users' (học viên trong lớp), 'members' (có thể là một quan hệ khác để lấy học viên, cần kiểm tra trong Model), 'sharedFlashcards.flashcardSet' (Flashcards được chia sẻ và tập Flashcard của chúng), và 'tests' (các bài kiểm tra trong lớp).
        // ->findOrFail($id) : Tìm lớp học với $id đã cho. Nếu không tìm thấy, sẽ tự động trả về lỗi 404.
        $classroom = ClassRoom::with([
            'users',
            'members',
            'sharedFlashcards.flashcardSet',
            'tests',
        ])->findOrFail($id);

        // Xác định phạm vi thời gian theo request('time_filter')
        $timeFilter = request('time_filter');
        $timeStart = null;

        if ($timeFilter === 'week') {
            $timeStart = now()->startOfWeek(); // Đầu tuần
        } elseif ($timeFilter === 'month') {
            $timeStart = now()->startOfMonth(); // Đầu tháng
        }

        // Kiểm tra quyền truy cập (nếu người dùng không phải giáo viên)
        if (auth()->user()->roles !== 'teacher') {
            // Kiểm tra xem người dùng hiện tại có phải là thành viên của lớp học không
            $isMember = $classroom->users->contains(auth()->id());
            if (! $isMember) {
                // Nếu không phải thành viên, chuyển hướng về trang lớp học của tôi với thông báo lỗi.
                return redirect()->route('classrooms.my')->with('error', 'Bạn đã bị xoá khỏi lớp học này.');
            }
        }

        // TRUY VẤN: Lấy lịch sử làm bài của từng học viên trong lớp
        // History::with(['test', 'user']) : Tải kèm thông tin bài kiểm tra và người dùng cho mỗi lịch sử.
        // ->whereIn('user_id', $classroom->members->pluck('id')) : Lọc các lịch sử mà 'user_id' nằm trong danh sách ID của các thành viên trong lớp.
        // ->whereHas('test.classrooms', fn($q) => $q->where('class_rooms.id', $classroom->id)) : Đảm bảo rằng lịch sử làm bài thuộc về một bài kiểm tra có liên kết với lớp học hiện tại. Đây là một truy vấn lồng.
        // ->get() : Thực thi truy vấn.
        // ->groupBy('user_id') : Nhóm kết quả theo 'user_id' để dễ dàng xử lý lịch sử của từng học viên.
        $histories = History::with(['test', 'user'])
            ->whereIn('user_id', $classroom->members->pluck('id'))
            ->whereHas('test.classrooms', fn($q) => $q->where('class_rooms.id', $classroom->id))
            ->when($timeStart, fn($q) => $q->where('created_at', '>=', $timeStart)) // 🔥 lọc theo thời gian
            ->get()
            ->groupBy('user_id');

        // TRUY VẤN: Tính điểm trung bình của học viên trong lớp
        // History::select('user_id', DB::raw('AVG(score) as avg_score')) : Chọn 'user_id' và tính điểm trung bình ('avg_score') từ cột 'score'. DB::raw cho phép viết SQL thuần.
        // ->whereHas('test.classrooms', fn($q) => $q->where('class_rooms.id', $classroom->id)) : Tương tự, đảm bảo lịch sử thuộc về bài kiểm tra trong lớp này.
        // ->groupBy('user_id') : Nhóm theo 'user_id' để tính điểm trung bình cho từng học viên.
        // ->pluck('avg_score', 'user_id') : Trích xuất cột 'avg_score' và sử dụng 'user_id' làm khóa của mảng kết quả, tạo ra một mảng [user_id => avg_score].
        $avgScores = History::select('user_id', DB::raw('AVG(score) as avg_score'))
            ->whereHas('test.classrooms', fn($q) => $q->where('class_rooms.id', $classroom->id))
            ->when($timeStart, fn($q) => $q->where('created_at', '>=', $timeStart)) // 🔥 lọc theo thời gian
            ->groupBy('user_id')
            ->pluck('avg_score', 'user_id');

        // Map thêm tên để truyền sang JS vẽ biểu đồ
        $avgScoresFull = $classroom->members->map(function ($student) use ($avgScores) {
            return [
                'name' => $student->name,
                'score' => round($avgScores[$student->id] ?? 0, 2),
            ];
        });

        // Tính toán thống kê cho từng bài kiểm tra trong lớp
        $testStats = $classroom->tests->map(function ($test) {
            $histories = $test->histories; // Tải lịch sử làm bài của từng bài kiểm tra thông qua quan hệ.

            return [
                'test_title' => $test->content,
                'total_attempts' => $histories->count(),
                'avg_score' => round($histories->avg('score'), 2),
                'highest_score' => $histories->max('score'),
                'lowest_score' => $histories->min('score'),
            ];
        });

        $members = $classroom->members; // Lấy danh sách thành viên trong lớp.

        $search = request('search'); // Lấy từ khóa tìm kiếm từ request
        if ($search) {
            // Lọc thành viên nếu có từ khóa tìm kiếm
            $members = $members->filter(function ($user) use ($search) {
                return str_contains(strtolower($user->name), strtolower($search)) ||
                    str_contains(strtolower($user->email), strtolower($search));
            });
        }

        $total = $members->count(); // Tổng số thành viên sau khi lọc.

        // TRUY VẤN: Lấy ID của các học viên đã hoàn thành bài kiểm tra trong lớp
        // History::whereHas('test.classrooms', fn($q) => $q->where('class_rooms.id', $classroom->id)) : Tìm lịch sử làm bài thuộc về bài kiểm tra trong lớp hiện tại.
        // ->pluck('user_id')->unique() : Lấy tất cả các 'user_id' từ các bản ghi lịch sử và loại bỏ các giá trị trùng lặp để có danh sách các học viên duy nhất đã làm bài.
        $completedUserIds = History::whereHas('test.classrooms', fn($q) => $q->where('class_rooms.id', $classroom->id))
            ->when($timeStart, fn($q) => $q->where('created_at', '>=', $timeStart))
            ->pluck('user_id')->unique();

        $done = $completedUserIds->count(); // Số lượng học viên đã hoàn thành.
        $notDone = $total - $done; // Số lượng học viên chưa hoàn thành.

        $totalTests = $classroom->tests->count(); // Tổng số bài kiểm tra trong lớp.

        // TRUY VẤN: Tính điểm trung bình của tất cả các bài kiểm tra trong lớp
        // History::whereHas('test.classrooms', fn($q) => $q->where('class_rooms.id', $classroom->id))->avg('score') : Tính điểm trung bình của tất cả các lịch sử làm bài thuộc về bài kiểm tra trong lớp hiện tại.
        $avgScoreAll = History::whereHas('test.classrooms', fn($q) => $q
            ->where('class_rooms.id', $classroom->id))
            ->when($timeStart, fn($q) => $q->where('created_at', '>=', $timeStart)) // 🔥 lọc theo thời gian
            ->avg('score');
        $completedCount = $done; // Đã định nghĩa ở trên, có thể trùng lặp.

        // Tính toán xếp loại cho từng thành viên
        $ratings = $members->map(function ($user) use ($histories) {
            $userHist = $histories->get($user->id); // Lấy lịch sử làm bài của học viên hiện tại.
            $avgScore100 = $userHist?->avg('score') ?? 0; // Điểm trung bình (hệ 100) của học viên, nếu không có lịch sử thì 0.
            $attempts = $userHist?->count() ?? 0; // Số lần làm bài của học viên, nếu không có lịch sử thì 0.

            // Quy đổi về hệ 10
            $avg = $avgScore100 / 10;

            // Xếp loại dựa trên điểm trung bình
            $rank = match (true) {
                $avg >= 8 => 'Giỏi',
                $avg >= 6 => 'Khá',
                $avg >= 4 => 'Trung bình',
                default => 'Yếu',
            };

            return [
                'name' => $user->name,
                'email' => $user->email,
                'avg' => number_format($avgScore100, 2), // Vẫn hiển thị điểm hệ 100 với 2 chữ số thập phân
                'attempts' => $attempts,
                'rank' => $rank,
            ];
        });

        $selectedTestId = request('test_id'); // Lấy ID bài kiểm tra được chọn từ filter

        // Lọc lại histories nếu có chọn test cụ thể
        if ($selectedTestId) {
            // TRUY VẤN: Lấy lịch sử làm bài cho một bài kiểm tra cụ thể và các thành viên trong lớp
            // History::with(['test', 'user']) : Tải kèm thông tin bài kiểm tra và người dùng.
            // ->where('test_id', $selectedTestId) : Lọc theo ID của bài kiểm tra được chọn.
            // ->whereIn('user_id', $classroom->members->pluck('id')) : Đảm bảo lịch sử làm bài thuộc về các thành viên trong lớp.
            // ->get() : Thực thi truy vấn.
            // ->groupBy('user_id') : Nhóm kết quả theo 'user_id'.
            $histories = History::with(['test', 'user'])
                ->where('test_id', $selectedTestId)
                ->whereIn('user_id', $classroom->members->pluck('id'))
                ->when($timeStart, fn($q) => $q->where('created_at', '>=', $timeStart))
                ->get()
                ->groupBy('user_id');
        }

        // Trả về view 'user.classrooms.show' và truyền tất cả các dữ liệu đã tính toán vào.
        return view('user.classrooms.show', compact(
            'classroom',
            'histories',
            'avgScores',
            'avgScoresFull',
            'testStats',
            'done',
            'notDone',
            'total',
            'totalTests',
            'avgScoreAll',
            'completedCount',
            'ratings',
            'selectedTestId',
            'timeStart',
            'members'
        ));
    }

    public function exportResults($id)
    {
        // TRUY VẤN: Tìm lớp học theo ID để xuất kết quả
        // Classroom::findOrFail($id) : Tìm lớp học với $id. Nếu không tìm thấy, sẽ trả về lỗi 404.
        $classroom = Classroom::findOrFail($id);
        // Xuất file Excel bằng ClassResultExport (một lớp Export được định nghĩa riêng).
        return Excel::download(new ClassResultExport($classroom), 'ket_qua_lop_' . $classroom->id . '.xlsx');
    }

    public function leave($id)
    {
        // TRUY VẤN: Tìm lớp học để người dùng rời đi
        // ClassRoom::findOrFail($id) : Tìm lớp học.
        $classroom = ClassRoom::findOrFail($id);
        // TRUY VẤN: Gỡ bỏ mối quan hệ giữa người dùng hiện tại và lớp học
        // $classroom->users()->detach(auth()->id()) : Sử dụng quan hệ many-to-many 'users' của lớp học để xóa bản ghi liên kết giữa lớp học này và người dùng đang đăng nhập khỏi bảng trung gian `classroom_users`.
        $classroom->users()->detach(auth()->id());
        // Chuyển hướng về trang lớp học của tôi với thông báo thành công.

        // Tạo thông báo cho học viên đã rời lớp
        Notification::create([
            'user_id' => auth()->id(), // ID của học viên hiện tại
            'title' => 'Bạn đã rời khỏi lớp học',
            'message' => "Bạn đã rời khỏi lớp học {$classroom->name}.",
        ]);
        return redirect()->route('classrooms.my')->with('success', 'Bạn đã rời lớp học thành công!');
    }

    public function removeStudent($classroomId, $userId)
    {
        // TRUY VẤN: Tìm lớp học để xóa học viên
        // ClassRoom::findOrFail($classroomId) : Tìm lớp học.
        $classroom = ClassRoom::findOrFail($classroomId);

        // Kiểm tra quyền: Đảm bảo người thực hiện là giáo viên tạo ra lớp này
        if ($classroom->teacher_id != auth()->id()) {
            abort(403); // Nếu không, trả về lỗi 403 (Forbidden)
        }

        // TRUY VẤN: Gỡ bỏ mối quan hệ giữa học viên và lớp học
        // $classroom->users()->detach($userId) : Xóa bản ghi liên kết giữa lớp học này và người dùng có $userId khỏi bảng trung gian `classroom_users`.
        $classroom->users()->detach($userId);

        // TRUY VẤN: Tìm người dùng (học viên) vừa bị xóa để gửi thông báo
        // User::find($userId) : Tìm người dùng với ID đã cho.
        $user = User::find($userId);

        // TRUY VẤN: Tạo thông báo cho học viên bị xóa khỏi lớp
        // Notification::create([...]) : Tạo một bản ghi thông báo mới trong bảng `notifications`.
        Notification::create([
            'user_id' => $userId, // ID của học viên nhận thông báo
            'title' => 'Bạn đã bị xóa khỏi lớp',
            'message' => "Bạn đã bị xóa khỏi lớp học {$classroom->name} bởi giáo viên.",
        ]);

        // Quay lại trang trước đó với thông báo thành công.
        return back()->with('success', 'Xoá học viên khỏi lớp thành công!');
    }

    public function inviteLink($code)
    {
        // Tạo một đối tượng Request mới với mã mời (code) để tái sử dụng logic của joinByCode.
        $request = new \Illuminate\Http\Request(['code' => $code]);
        return $this->joinByCode($request); // Gọi phương thức joinByCode.
    }

    /**
     * 4. Hiển thị form để học viên nhập mã lớp và tham gia (GET)
     * - View gồm 1 input để nhập mã lớp
     */
    public function joinForm()
    {
        // Trả về view để hiển thị form nhập mã lớp.
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

        if (!$classroom) {
            return back()->withErrors(['code' => 'Mã lớp không tồn tại.']);
        }

        $alreadyJoined = ClassroomUser::where('classroom_id', $classroom->id)
            ->where('user_id', auth()->id())
            ->exists();

        if ($alreadyJoined) {
            return back()->with('info', 'Bạn đã tham gia lớp này rồi.');
        }

        ClassroomUser::create([
            'classroom_id' => $classroom->id,
            'user_id' => auth()->id(),
        ]);

        $user = auth()->user();
        $teacher = $classroom->teacher; // ← dùng đúng quan hệ đã sửa

        // Tạo thông báo cho giáo viên nếu khác học viên
        if ($teacher && $teacher->id !== $user->id) {

            // Thông báo cho giáo viên
            Notification::create([
                'user_id' => $teacher->id,
                'title' => '📥 Học viên mới',
                'message' => $user->name . ' đã tham gia lớp "' . $classroom->name . '"',
            ]);

            // Thông báo cho học viên
            Notification::create([
                'user_id' => $user->id,
                'title' => '🎉 Tham gia lớp học',
                'message' => 'Bạn đã tham gia lớp "' . $classroom->name . '" thành công.',
            ]);
        }

        return redirect()->route('classrooms.my')->with('success', 'Tham gia lớp học thành công!');
    }

    /**
     * 6. Hiển thị danh sách lớp học mà học viên đã tham gia
     * - Lấy từ quan hệ many-to-many giữa User và ClassRoom
     * - Cần định nghĩa hàm joinedClassrooms trong model User
     */
    public function myClassrooms(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = auth()->user();
        $query = $user->joinedClassrooms();

        if ($request->has('search') && $request->search) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $classrooms = $query->get();

        return view('user.classrooms_user.my', compact('classrooms'));
    }

    public function notifyIncompleteStudents($id)
    {
        // TRUY VẤN: Tìm lớp học và tải kèm các thành viên
        // ClassRoom::with('members')->findOrFail($id) : Tìm lớp học và tải danh sách thành viên của lớp.
        $classroom = ClassRoom::with('members')->findOrFail($id);

        // Kiểm tra quyền: Đảm bảo người thực hiện là giáo viên tạo ra lớp này
        if ($classroom->teacher_id !== auth()->id()) {
            abort(403);
        }

        // TRUY VẤN: Lấy danh sách ID của học viên đã có lịch sử làm bài trong lớp này
        // $classroom->tests->flatMap(function ($test) { return $test->histories->pluck('user_id'); }) : Duyệt qua từng bài kiểm tra trong lớp, sau đó từ mỗi bài kiểm tra, lấy tất cả 'user_id' từ lịch sử làm bài của bài đó. flatMap sẽ gom tất cả các mảng user_id thành một mảng phẳng.
        // ->unique() : Loại bỏ các ID trùng lặp để có danh sách các học viên duy nhất đã làm bài.
        $completedStudentIds = $classroom->tests->flatMap(function ($test) {
            return $test->histories->pluck('user_id');
        })->unique();

        // Lấy danh sách học viên chưa làm bài
        // $classroom->members->filter(...) : Lọc danh sách thành viên của lớp.
        // !$completedStudentIds->contains($student->id) : Giữ lại những học viên mà ID của họ không nằm trong danh sách các ID đã hoàn thành bài.
        $incompleteStudents = $classroom->members->filter(function ($student) use ($completedStudentIds) {
            return !$completedStudentIds->contains($student->id);
        });

        $notifiedCount = 0;

        foreach ($incompleteStudents as $student) {
            // TRUY VẤN: Tạo thông báo cho từng học viên chưa làm bài
            // $student->customNotifications()->create([...]) : Sử dụng quan hệ `customNotifications` của học viên để tạo thông báo mới.
            $student->notifications()->create([
                'title' => '📌 Nhắc nhở làm bài kiểm tra',
                'message' => 'Bạn chưa hoàn thành bài kiểm tra trong lớp "' . $classroom->name . '". Vui lòng làm bài sớm nhé!',
                'url' => route('classrooms.show', $classroom->id),
            ]);
            $notifiedCount++;
        }

        // Quay lại trang trước đó với thông báo số lượng học viên đã được thông báo.
        return back()->with('success', "Đã gửi thông báo đến $notifiedCount học viên chưa làm bài.");
    }
}
