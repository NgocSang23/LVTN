@extends('user.master')

@section('title', 'Chi tiết lớp học')

@section('content')
    <div class="container py-4">
        {{-- Thông tin lớp học --}}
        <div class="card shadow-sm border-0 rounded-4 mb-4">
            <div class="card-body d-flex justify-content-between align-items-start flex-wrap gap-3">
                <div>
                    <h2 class="fw-bold text-primary mb-2">{{ $classroom->name }}</h2>
                    <p class="mb-1">
                        <span class="text-body-secondary me-2">Mã lớp:</span>
                        <span class="badge bg-secondary text-white fw-semibold px-2 py-1">{{ $classroom->code }}</span>
                    </p>
                    <p class="text-muted mb-0">{{ $classroom->description ?: 'Không có mô tả' }}</p>
                </div>
                <div class="text-md-end">
                    <span class="badge bg-light text-dark border border-info px-3 py-2 rounded-pill shadow-sm me-2">
                        Giáo viên: {{ $classroom->teacher->name }}
                    </span>
                    <span class="badge bg-light text-dark border border-info px-3 py-2 rounded-pill shadow-sm">
                        {{ $classroom->users->count() }} học viên
                    </span>
                </div>
            </div>
        </div>

        {{-- Thống kê nhanh --}}
        @can('teacher')
            <div class="row row-cols-1 row-cols-md-3 g-3 mb-4">
                <div class="col">
                    <div class="card h-100 shadow-sm border-0 text-center">
                        <div class="card-body">
                            <h4 class="fw-bold text-primary">{{ $total }}</h4>
                            <p class="text-muted mb-0">Tổng học viên</p>
                        </div>
                    </div>
                </div>
                <div class="col">
                    <div class="card h-100 shadow-sm border-0 text-center">
                        <div class="card-body">
                            <h4 class="fw-bold text-success">{{ number_format($avgScoreAll, 2) }}</h4>
                            <p class="text-muted mb-0">Điểm trung bình lớp</p>
                        </div>
                    </div>
                </div>
                <div class="col">
                    <div class="card h-100 shadow-sm border-0 text-center">
                        <div class="card-body">
                            <h4 class="fw-bold text-warning">{{ $completedCount }}/{{ $total }}</h4>
                            <p class="text-muted mb-0">Đã làm bài kiểm tra</p>
                        </div>
                    </div>
                </div>
            </div>
        @endcan

        {{-- Nút chức năng --}}
        <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
            @can('teacher')
                <a href="{{ route('flashcard_multiple_choice.create', ['classroom_id' => $classroom->id]) }}"
                    class="btn btn-primary rounded-pill shadow-sm">
                    <i class="fa-solid fa-file-circle-plus me-1"></i> Tạo bài kiểm tra mới
                </a>
            @endcan

            @can('student')
                <button class="btn btn-outline-danger rounded-pill shadow-sm" data-bs-toggle="modal"
                    data-bs-target="#leaveClassModal">
                    <i class="fa-solid fa-door-open me-1"></i> Rời lớp học
                </button>
            @endcan
        </div>

        {{-- Tabs điều hướng --}}
        <ul class="nav nav-tabs nav-fill mb-4 border-0 shadow-sm rounded-3 overflow-hidden" id="classroomTabs"
            role="tablist">
            @can('teacher')
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="students-tab" data-bs-toggle="tab" data-bs-target="#students"
                        type="button" role="tab" aria-controls="students" aria-selected="true">
                        👨‍🎓 Học viên
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="results-tab" data-bs-toggle="tab" data-bs-target="#results" type="button"
                        role="tab" aria-controls="results" aria-selected="false">
                        📊 Kết quả
                    </button>
                </li>
            @endcan
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="flashcard-tab" data-bs-toggle="tab" data-bs-target="#flashcardTab"
                    type="button" role="tab" aria-controls="flashcardTab" aria-selected="false">
                    📚 Flashcard
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="test-tab" data-bs-toggle="tab" data-bs-target="#testTab" type="button"
                    role="tab" aria-controls="testTab" aria-selected="false">
                    📝 Bài kiểm tra
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="assignment-tab" data-bs-toggle="tab" data-bs-target="#assignmentTab"
                    type="button" role="tab" aria-controls="assignmentTab" aria-selected="false">
                    📌 {{ auth()->user()->roles === 'teacher' ? 'Bài tập đã giao' : 'Bài tập được giao' }}
                </button>
            </li>
        </ul>

        {{-- ===== TAB CONTENT: Nội dung từng tab ===== --}}
        <div class="tab-content" id="classroomTabsContent">

            {{-- ==== TAB: DANH SÁCH HỌC VIÊN ==== --}}
            <div class="tab-pane fade show active" id="students" role="tabpanel" aria-labelledby="students-tab">
                @can('teacher')
                    {{-- Form tìm kiếm học viên --}}
                    <form method="GET" class="mb-3">
                        <input type="text" name="search" value="{{ request('search') }}" class="form-control"
                            placeholder="🔍 Tìm theo tên hoặc email...">
                    </form>

                    {{-- Danh sách học viên --}}
                    <h4 class="fw-semibold mb-3">📋 Danh sách học viên</h4>

                    @if ($classroom->members->count())
                        <div class="table-responsive" style="max-height: 500px;">
                            <table class="table table-bordered table-hover table-striped align-middle text-center shadow-sm">
                                <thead class="table-dark">
                                    <tr>
                                        <th style="width: 50px;">#</th>
                                        <th>👤 Họ tên</th>
                                        <th>📧 Email</th>
                                        <th>📅 Ngày tham gia</th>
                                        <th>⚙️ Thao tác</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($members as $index => $user)
                                        <tr>
                                            <td>{{ $index + 1 }}</td>
                                            <td>{{ $user->name }}</td>
                                            <td>{{ $user->email }}</td>
                                            <td>{{ optional($user->pivot->created_at)->format('d/m/Y') ?? 'Không rõ' }}</td>
                                            <td>
                                                <button class="btn btn-sm btn-outline-danger" data-bs-toggle="modal"
                                                    data-bs-target="#removeStudentModal"
                                                    onclick="prepareRemoveStudent({{ $classroom->id }}, {{ $user->id }})">
                                                    <i class="fa-solid fa-user-xmark me-1"></i> Xoá
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="alert alert-info">Chưa có học viên nào tham gia lớp học này.</div>
                    @endif

                    {{-- Lọc học viên theo xếp loại học viên --}}
                    <form method="GET" class="row row-cols-md-auto g-2 align-items-center mb-3">
                        <div class="col">
                            <label for="rank" class="form-label mb-0 small">Xếp loại</label>
                            <select name="rank" id="rank" class="form-select">
                                <option value="">-- Tất cả --</option>
                                <option value="Giỏi" {{ request('rank') == 'Giỏi' ? 'selected' : '' }}>Giỏi</option>
                                <option value="Khá" {{ request('rank') == 'Khá' ? 'selected' : '' }}>Khá</option>
                                <option value="Trung bình" {{ request('rank') == 'Trung bình' ? 'selected' : '' }}>Trung bình
                                </option>
                                <option value="Yếu" {{ request('rank') == 'Yếu' ? 'selected' : '' }}>Yếu</option>
                            </select>
                        </div>

                        <div class="col">
                            <button type="submit" class="btn btn-outline-primary mt-3 mt-md-4">
                                <i class="fa-solid fa-filter me-1"></i> Lọc
                            </button>
                        </div>
                    </form>

                    {{-- Bảng xếp loại học viên --}}
                    <hr>
                    <h4 class="fw-semibold mb-3">📊 Bảng xếp loại học viên</h4>
                    <div class="table-responsive" style="max-height: 500px;">
                        <table class="table table-bordered table-hover table-striped align-middle text-center shadow-sm">
                            <thead class="table-dark">
                                <tr>
                                    <th>👤 Học viên</th>
                                    <th>📧 Email</th>
                                    <th>📈 Điểm TB</th>
                                    <th>📝 Làm bài</th>
                                    <th>🏅 Xếp loại</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $filteredRatings = $ratings->filter(function ($r) {
                                        $rank = request('rank');
                                        return empty($rank) || $r['rank'] === $rank;
                                    });
                                @endphp
                                @foreach ($filteredRatings as $r)
                                    @php
                                        $color = match ($r['rank']) {
                                            'Giỏi' => 'success',
                                            'Khá' => 'primary',
                                            'Trung bình' => 'warning',
                                            default => 'danger',
                                        };
                                    @endphp
                                    <tr>
                                        <td>{{ $r['name'] }}</td>
                                        <td>{{ $r['email'] }}</td>
                                        <td>{{ $r['avg'] }}</td>
                                        <td>{{ $r['attempts'] }} lần</td>
                                        <td><span class="badge bg-{{ $color }}">{{ $r['rank'] }}</span></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endcan
            </div>

            {{-- ==== TAB: KẾT QUẢ HỌC TẬP ==== --}}
            <div class="tab-pane fade" id="results" role="tabpanel" aria-labelledby="results-tab">
                @can('teacher')
                    {{-- Export + Nhắc nhở --}}
                    <div class="d-flex justify-content-end flex-wrap mb-3 gap-2">
                        <a href="{{ route('classrooms.export', $classroom->id) }}" class="btn btn-success">
                            <i class="fa-solid fa-download me-1"></i> Tải Excel
                        </a>
                        <button type="button" class="btn btn-warning" data-bs-toggle="modal"
                            data-bs-target="#notifyIncompleteModal">
                            <i class="fa-solid fa-bell me-1"></i> Nhắc chưa làm bài
                        </button>
                    </div>

                    {{-- Bộ lọc bài kiểm tra + thời gian --}}
                    <form method="GET" class="mb-3">
                        <div class="row g-2 align-items-end">
                            <div class="col-auto">
                                <label for="test_id" class="form-label mb-0 small">Bài kiểm tra</label>
                                <select name="test_id" id="test_id" class="form-select">
                                    <option value="">-- Tất cả bài kiểm tra --</option>
                                    @foreach ($classroom->tests as $test)
                                        <option value="{{ $test->id }}"
                                            {{ request('test_id') == $test->id ? 'selected' : '' }}>
                                            {{ $test->content }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-auto">
                                <label for="time_filter" class="form-label mb-0 small">Thời gian</label>
                                <select name="time_filter" id="time_filter" class="form-select">
                                    <option value="">-- Tất cả thời gian --</option>
                                    <option value="week" {{ request('time_filter') == 'week' ? 'selected' : '' }}>Tuần này
                                    </option>
                                    <option value="month" {{ request('time_filter') == 'month' ? 'selected' : '' }}>Tháng này
                                    </option>
                                </select>
                            </div>

                            <div class="col-auto d-flex gap-2">
                                <button type="submit" class="btn btn-outline-primary">
                                    <i class="fa-solid fa-filter me-1"></i> Lọc
                                </button>
                                <a href="{{ route('classrooms.show', $classroom->id) }}#results"
                                    class="btn btn-outline-secondary">
                                    <i class="fa-solid fa-rotate-left me-1"></i> Xoá bộ lọc
                                </a>
                            </div>
                        </div>
                    </form>

                    {{-- Bảng kết quả học tập --}}
                    <h4 class="fw-semibold mb-3">📊 Kết quả học tập</h4>
                    @if ($histories->count())
                        <div class="table-responsive" style="max-height: 500px;">
                            <table class="table table-hover table-bordered table-striped text-center shadow-sm">
                                <thead class="table-dark">
                                    <tr>
                                        <th>#</th>
                                        <th>👤 Học viên</th>
                                        <th>🌟 Trung bình</th>
                                        <th>📝 Bài kiểm tra</th>
                                        <th>✅ Đúng</th>
                                        <th>❌ Sai</th>
                                        <th>📈 Điểm</th>
                                        <th>⏳ Thời gian nộp</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php $rowNumber = 1; @endphp
                                    @foreach ($classroom->members as $student)
                                        @if ($histories->has($student->id))
                                            @foreach ($histories[$student->id] as $record)
                                                <tr>
                                                    <td>{{ $rowNumber++ }}</td>
                                                    <td>{{ $student->name }}</td>
                                                    <td>{{ number_format($avgScores[$student->id] ?? 0, 2) }}</td>
                                                    <td>{{ $record->test->content ?? 'N/A' }}</td>
                                                    <td>{{ $record->correct_count }} / {{ $record->total_questions }}</td>
                                                    <td>{{ $record->total_questions - $record->correct_count }}</td>
                                                    <td>{{ $record->score }}</td>
                                                    <td>{{ \Carbon\Carbon::parse($record->created_at)->timezone('Asia/Ho_Chi_Minh')->format('d/m/Y H:i') }}
                                                    </td>
                                                </tr>
                                            @endforeach
                                        @else
                                            <tr>
                                                <td>{{ $rowNumber++ }}</td>
                                                <td>{{ $student->name }}</td>
                                                <td colspan="6" class="text-muted fst-italic">Chưa làm bài nào</td>
                                            </tr>
                                        @endif
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="alert alert-info">Chưa có học viên nào làm bài kiểm tra.</div>
                    @endif

                    {{-- Phân tích theo bài kiểm tra --}}
                    <h5 class="fw-bold mt-4 mb-3">📋 Phân tích theo từng bài kiểm tra</h5>
                    @if ($testStats->count())
                        <table class="table table-hover table-bordered table-striped shadow-sm text-center">
                            <thead class="table-dark">
                                <tr>
                                    <th>#</th>
                                    <th>📝 Tên bài</th>
                                    <th>👥 Số lượt làm</th>
                                    <th>📈 Điểm TB</th>
                                    <th>🔼 Cao nhất</th>
                                    <th>🔽 Thấp nhất</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($testStats as $index => $stat)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>{{ $stat['test_title'] }}</td>
                                        <td>{{ $stat['total_attempts'] }}</td>
                                        <td>{{ $stat['avg_score'] }}</td>
                                        <td>{{ $stat['highest_score'] }}</td>
                                        <td>{{ $stat['lowest_score'] }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @else
                        <div class="alert alert-info">Chưa có bài kiểm tra nào để thống kê.</div>
                    @endif

                    <div class="row g-4 mt-4">
                        <div class="col-lg-6">
                            <div class="card shadow-sm rounded-3 p-3 h-100">
                                <h4 class="fw-bold text-dark mb-3 text-center">📉 Biểu đồ điểm trung bình học viên</h4>
                                <div class="chart-container" style="height: 300px;">
                                    <canvas id="avgScoreChart"></canvas>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="card shadow-sm rounded-3 p-3 h-100">
                                <h4 class="fw-bold text-dark mb-3 text-center">📊 Tỷ lệ hoàn thành bài kiểm tra</h4>
                                <div class="chart-container d-flex justify-content-center align-items-center"
                                    style="height: 300px;">
                                    <canvas id="completionPie" width="300" height="300"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Nhúng thư viện Chart.js --}}
                    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
                    <script>
                        document.addEventListener('DOMContentLoaded', function() {
                            // Average Score Chart
                            const ctx = document.getElementById('avgScoreChart');
                            if (ctx) {
                                new Chart(ctx, {
                                    type: 'bar',
                                    data: {
                                        labels: {!! json_encode($avgScoresFull->pluck('name')) !!},
                                        datasets: [{
                                            label: 'Điểm trung bình',
                                            data: {!! json_encode($avgScoresFull->pluck('score')) !!},
                                            backgroundColor: 'rgba(54, 162, 235, 0.7)',
                                            borderColor: 'rgba(54, 162, 235, 1)',
                                            borderWidth: 1,
                                            borderRadius: 8
                                        }]
                                    },
                                    options: {
                                        responsive: true,
                                        maintainAspectRatio: false,
                                        scales: {
                                            y: {
                                                beginAtZero: true,
                                                max: 10,
                                                grid: {
                                                    color: 'rgba(0, 0, 0, 0.05)'
                                                }
                                            },
                                            x: {
                                                grid: {
                                                    display: false
                                                }
                                            }
                                        },
                                        plugins: {
                                            legend: {
                                                display: false
                                            },
                                            tooltip: {
                                                backgroundColor: 'rgba(0, 0, 0, 0.8)',
                                                titleColor: '#fff',
                                                bodyColor: '#fff'
                                            }
                                        }
                                    }
                                });
                            }

                            // Completion Pie Chart
                            const ctxPie = document.getElementById('completionPie');
                            if (ctxPie) {
                                new Chart(ctxPie, {
                                    type: 'pie',
                                    data: {
                                        labels: ['Đã hoàn thành', 'Chưa làm'],
                                        datasets: [{
                                            data: [{{ $done }}, {{ $notDone }}],
                                            backgroundColor: ['#28a745', '#dc3545'],
                                            borderColor: '#fff',
                                            borderWidth: 2
                                        }]
                                    },
                                    options: {
                                        responsive: true,
                                        maintainAspectRatio: false,
                                        plugins: {
                                            legend: {
                                                position: 'bottom',
                                                labels: {
                                                    font: {
                                                        size: 14
                                                    }
                                                }
                                            },
                                            tooltip: {
                                                backgroundColor: 'rgba(0, 0, 0, 0.8)',
                                                titleColor: '#fff',
                                                bodyColor: '#fff'
                                            }
                                        }
                                    }
                                });
                            }
                        });
                    </script>
                @endcan
            </div>

            {{-- ==== TAB: FLASHCARD CHIA SẺ ==== --}}
            <div class="tab-pane fade" id="flashcardTab" role="tabpanel" aria-labelledby="flashcard-tab">
                <h4 class="fw-semibold mb-3">📚 Bộ flashcard được chia sẻ</h4>
                @php $sharedSets = $classroom->sharedFlashcards->unique('flashcard_set_id'); @endphp

                @if ($sharedSets->count())
                    <div class="row" style="max-height: 500px; overflow-y: auto;">
                        @foreach ($sharedSets as $item)
                            @php $set = $item->flashcardSet; @endphp
                            @if ($set && !empty($set->question_ids))
                                <div class="col-md-6 col-lg-4 mb-4">
                                    <div class="card shadow-sm h-100 border-0" style="border-radius: 14px;">
                                        <div class="card-body d-flex flex-column justify-content-between">
                                            <div>
                                                <h5 class="fw-bold text-primary">{{ $set->title }}</h5>
                                                <p class="text-muted mb-1">{{ $set->description ?? 'Không có mô tả' }}</p>
                                            </div>

                                            <div class="mt-3 text-end">
                                                <a href="{{ route('user.flashcard_define_essay', ['ids' => $set->question_ids]) }}"
                                                    class="btn btn-sm btn-outline-primary me-2">
                                                    <i class="fa-solid fa-eye me-1"></i> Xem bộ thẻ
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        @endforeach
                    </div>
                @else
                    <div class="alert alert-info">Chưa có bộ flashcard nào được chia sẻ.</div>
                @endif
            </div>

            {{-- ==== TAB: BÀI KIỂM TRA CHIA SẺ ==== --}}
            <div class="tab-pane fade" id="testTab" role="tabpanel" aria-labelledby="assignment-tab">
                @php
                    $tests = $classroom->tests->sortByDesc('created_at');
                @endphp

                {{-- Hiển thị BÀI KIỂM TRA --}}
                @if ($tests->count())
                    <h4 class="fw-semibold mb-3">📝 Bài kiểm tra đã chia sẻ</h4>
                    <div class="row row-cols-1 row-cols-md-2 g-4">
                        @foreach ($tests as $test)
                            <div class="col-md-6 col-lg-4 mb-4">
                                <div class="card shadow-sm border-0">
                                    <div class="card-body d-flex flex-column justify-content-between">
                                        <div>
                                            <h5 class="fw-bold text-dark">📝 {{ $test->content }}</h5>
                                            <p class="text-muted mb-1">⏱
                                                {{ \Carbon\Carbon::parse($test->time)->format('i') }} phút</p>
                                            <p class="text-muted small mb-0">👤 {{ $test->user->name ?? 'Không rõ' }}</p>
                                        </div>
                                        <div class="mt-3 text-end">
                                            <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal"
                                                data-bs-target="#confirmTestModal"
                                                onclick="showTestModal(
                                                        '{{ $test->id }}',
                                                        '{{ $test->content }}',
                                                        '{{ \Carbon\Carbon::parse($test->time)->format('i') }}',
                                                        '{{ $test->user->name ?? 'Không rõ' }}',
                                                        '{{ $test->created_at->format('d/m/Y') }}',
                                                        '{{ $test->questionNumbers->first()->question_number ?? 'Không có' }}',
                                                        '{{ route('flashcard_multiple_choice.show', $test->id) }}'
                                                    )">
                                                <i class="fa-solid fa-eye me-1"></i> Xem chi tiết
                                            </button>
                                            @can('teacher')
                                                <button class="btn btn-sm btn-outline-success" data-bs-toggle="modal"
                                                    data-bs-target="#assignTestModal"
                                                    onclick="prepareTestAssignment('{{ $test->id }}', '{{ $classroom->id }}')">
                                                    <i class="fa-solid fa-paper-plane me-1"></i> Giao lại
                                                </button>
                                            @endcan
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif

                {{-- Nếu cả hai đều rỗng --}}
                @if ($tests->isEmpty())
                    <div class="alert alert-info mt-3">Chưa có bài tập nào được giao cho lớp này.</div>
                @endif
            </div>

            {{-- ==== TAB: BÀI TẬP GIAO ==== --}}
            <div class="tab-pane fade" id="assignmentTab" role="tabpanel" aria-labelledby="test-tab">
                <h4 class="fw-semibold mb-3">📝 Bài kiểm tra trắc nghiệm</h5>
                    @if ($classroom->tests->count())
                        <div class="row" style="max-height: 500px; overflow-y: auto;">
                            @foreach ($classroom->tests as $test)
                                @php
                                    // Lấy deadline và kiểm tra đã hết hạn hay chưa
                                    $deadline = \Carbon\Carbon::parse($test->pivot->deadline);
                                    $now = \Carbon\Carbon::now();
                                    $isExpired = $deadline->isPast();
                                @endphp

                                <div class="col-md-6 col-lg-4 mb-4">
                                    <div class="card shadow-sm h-100 border-0" style="border-radius: 14px;">
                                        <div class="card-body d-flex flex-column justify-content-between">
                                            <div>
                                                <h5 class="fw-bold text-dark">📝 {{ $test->content }}</h5>
                                                <p class="text-muted mb-1">⏱
                                                    {{ \Carbon\Carbon::parse($test->time)->format('i') }} phút</p>
                                                <p class="text-muted mb-1">📅 Hạn nộp: {{ $deadline }}</p>
                                                <p class="text-muted small mb-0">👤 {{ $test->user->name ?? 'Không rõ' }}
                                                </p>
                                            </div>
                                            <div class="mt-3 text-end">
                                                @if (!$isExpired)
                                                    <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal"
                                                        data-bs-target="#confirmTestModal"
                                                        onclick="showTestModal(
                                                    '{{ $test->id }}',
                                                    '{{ $test->content }}',
                                                    '{{ \Carbon\Carbon::parse($test->time)->format('i') }}',
                                                    '{{ $test->user->name ?? 'Không rõ' }}',
                                                    '{{ $test->created_at->format('d/m/Y') }}',
                                                    '{{ $test->questionNumbers->first()->question_number ?? 'Không có' }}',
                                                    '{{ route('flashcard_multiple_choice.show', $test->id) }}'
                                                )">
                                                        <i class="fa-solid fa-eye me-1"></i> Xem chi tiết
                                                    </button>
                                                @else
                                                    <button class="btn btn-sm btn-secondary" disabled>
                                                        <i class="fa-solid fa-lock me-1"></i> Đã hết hạn
                                                    </button>
                                                @endif

                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="alert alert-info">Chưa có bài kiểm tra nào được chia sẻ cho lớp học.</div>
                    @endif
            </div>
        </div>
    </div>

    {{-- Modal giao bài kiểm tra --}}
    <div class="modal fade" id="assignTestModal" tabindex="-1" aria-labelledby="assignTestModalLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <form method="POST" action="{{ route('classroom_tests.assign') }}" class="modal-content">
                @csrf
                <input type="hidden" name="test_id" id="assign_test_id">
                <input type="hidden" name="classroom_id" id="assign_classroom_id">

                <div class="modal-header">
                    <h5 class="modal-title" id="assignTestModalLabel">📤 Giao bài kiểm tra</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <div class="mb-3">
                        <label for="deadline" class="form-label">📅 Hạn nộp</label>
                        <input type="datetime-local" name="deadline" class="form-control" required>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Huỷ</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fa-solid fa-paper-plane me-1"></i> Giao bài kiểm tra
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal xác nhận gửi thông báo -->
    <div class="modal fade" id="notifyIncompleteModal" tabindex="-1" aria-labelledby="notifyIncompleteModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content shadow">
                <div class="modal-header bg-warning text-dark">
                    <h5 class="modal-title" id="notifyIncompleteModalLabel">
                        <i class="fa-solid fa-triangle-exclamation me-2"></i> Xác nhận gửi thông báo
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Đóng"></button>
                </div>
                <div class="modal-body">
                    Bạn có chắc chắn muốn gửi thông báo đến <strong>các học viên chưa làm bài kiểm tra</strong>?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Huỷ</button>

                    <form method="POST" action="{{ route('classrooms.notifyIncomplete', $classroom->id) }}">
                        @csrf
                        <button type="submit" class="btn btn-warning">
                            <i class="fa-solid fa-paper-plane me-1"></i> Gửi thông báo
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Xác Nhận Làm Bài Kiểm Tra -->
    <div class="modal fade" id="confirmTestModal" tabindex="-1" aria-labelledby="confirmTestLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 rounded-3">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title fw-bold" id="confirmTestLabel">
                        <i class="bi bi-patch-question-fill me-2"></i> Xác nhận làm bài kiểm tra
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p><strong>📌 Nội dung:</strong> <span id="testTopic" class="fw-semibold"></span></p>
                    <p><strong>⏳ Thời gian:</strong> <span id="testTime" class="fw-semibold"></span> phút</p>
                    <p><strong>📖 Số câu hỏi:</strong> <span id="testQuestions" class="fw-semibold"></span> câu</p>
                    <p><strong>👤 Tác giả:</strong> <span id="testAuthor" class="fw-semibold"></span></p>
                    <p><strong>📅 Ngày tạo:</strong> <span id="testDate" class="fw-semibold"></span></p>
                    <div class="alert alert-warning d-flex align-items-center p-2 mt-3" role="alert">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i>
                        <span>Bạn có chắc chắn muốn bắt đầu bài kiểm tra?</span>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle"></i> Huỷ
                    </button>
                    <a id="startTestButton" href="#" class="btn btn-primary">
                        <i class="bi bi-play-circle"></i> Bắt đầu
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal: Xác nhận xoá học viên -->
    <div class="modal fade" id="removeStudentModal" tabindex="-1" aria-labelledby="removeStudentLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content shadow">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title" id="removeStudentLabel">
                        <i class="fa-solid fa-triangle-exclamation me-2"></i> Xoá học viên
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    Bạn có chắc chắn muốn <strong>xóa học viên này</strong> khỏi lớp học?
                </div>
                <div class="modal-footer">
                    <form id="removeStudentForm" method="POST">
                        @csrf
                        @method('DELETE')
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Huỷ</button>
                        <button type="submit" class="btn btn-danger">Xoá</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal: Rời khỏi lớp học -->
    <div class="modal fade" id="leaveClassModal" tabindex="-1" aria-labelledby="leaveClassLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content shadow">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title" id="leaveClassLabel"><i class="fa-solid fa-door-open me-2"></i> Rời khỏi lớp
                        học
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Đóng"></button>
                </div>
                <div class="modal-body">
                    Bạn có chắc chắn muốn <strong>rời khỏi lớp học này</strong> không?
                </div>
                <div class="modal-footer">
                    <form id="leaveClassForm" method="POST" action="{{ route('classrooms.leave', $classroom->id) }}">
                        @csrf
                        @method('DELETE')
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Huỷ</button>
                        <button type="submit" class="btn btn-danger text-white">Rời lớp</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        function prepareTestAssignment(testId, classroomId) {
            document.getElementById('assign_test_id').value = testId;
            document.getElementById('assign_classroom_id').value = classroomId;
        }

        document.addEventListener('DOMContentLoaded', function() {
            // Nếu có tab đã lưu từ trước thì mở tab đó
            const lastTab = localStorage.getItem('activeTab');
            if (lastTab) {
                const tabTrigger = document.querySelector(`button[data-bs-target="${lastTab}"]`);
                if (tabTrigger) {
                    new bootstrap.Tab(tabTrigger).show();
                }
            }

            // Cập nhật tab mỗi lần người dùng chuyển
            const tabButtons = document.querySelectorAll('button[data-bs-toggle="tab"]');
            tabButtons.forEach(button => {
                button.addEventListener('shown.bs.tab', function(event) {
                    const target = event.target.getAttribute('data-bs-target');
                    localStorage.setItem('activeTab', target);
                });
            });
        });

        function prepareRemoveStudent(classroomId, userId) {
            const form = document.getElementById('removeStudentForm');
            form.action = `/user/classrooms/${classroomId}/remove-student/${userId}`;
        }

        function showTestModal(id, content, time, author, date, questionCount, link) {
            document.getElementById('testTopic').textContent = content;
            document.getElementById('testTime').textContent = time;
            document.getElementById('testAuthor').textContent = author;
            document.getElementById('testDate').textContent = date;
            document.getElementById('testQuestions').textContent = questionCount;
            document.getElementById('startTestButton').href = link;
        }
    </script>
@endsection
