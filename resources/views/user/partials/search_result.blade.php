<!-- 📘 Khái niệm / Định nghĩa -->
<div class="mb-5">
    <h2 class="h4 mb-4">📘 Khái niệm / Định nghĩa</h2>

    {{-- 👤 Của bạn --}}
    @if ($my_flashcards->isNotEmpty())
        <div class="mb-3">
            <h5 class="fw-semibold">👤 Thẻ của bạn</h5>
            <div class="row g-4">
                @foreach ($my_flashcards as $card_define)
                    @include('user.partials._flashcard_card', ['card_define' => $card_define])
                @endforeach
            </div>
        </div>
    @endif

    {{-- 🌐 Cộng đồng --}}
    @if ($community_flashcards->isNotEmpty())
        <div class="mb-3">
            <h5 class="fw-semibold">🌐 Từ cộng đồng</h5>
            <div class="row g-4">
                @foreach ($community_flashcards as $card_define)
                    @include('user.partials._flashcard_card', ['card_define' => $card_define])
                @endforeach
            </div>
        </div>
    @endif

    @if ($my_flashcards->isEmpty() && $community_flashcards->isEmpty())
        <p class="text-muted">Không có thẻ nào để hiển thị.</p>
    @endif
</div>


@can('teacher')
    <!-- 🧠 Bài kiểm tra -->
    <div class="mb-5">
        <h2 class="h4 mb-4">🧠 Bài kiểm tra</h2>

        @if ($tests->isEmpty())
            <p class="text-muted">Không có bài kiểm tra nào để hiển thị.</p>
        @else
            <div class="row g-4">
                @foreach ($tests as $test)
                    <div class="col-12 col-sm-6 col-lg-4">
                        <div class="position-relative">
                            {{-- Dropdown chia sẻ nếu có lớp --}}
                            @if (auth()->user()->roles === 'teacher' && $myClassrooms->isNotEmpty())
                                <div class="dropdown position-absolute top-0 end-0 p-3" style="z-index: 1;">
                                    <span role="button" data-bs-toggle="dropdown"
                                        style="cursor: pointer; font-size: 20px;">⋮</span>
                                    <ul class="dropdown-menu dropdown-menu-end">
                                        <li>
                                            <a class="dropdown-item text-primary" href="javascript:void(0);"
                                                data-bs-toggle="modal" data-bs-target="#assignModal_{{ $test->id }}">
                                                📤 Giao bài kiểm tra
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            @endif

                            {{-- Nội dung thẻ bài kiểm tra --}}
                            <a href="javascript:void(0);" class="text-decoration-none text-dark"
                                onclick="showConfirmModal(
                                '{{ $test->id }}',
                                '{{ optional($test->questionNumbers->first()->topic)->title ?? 'Không có' }}',
                                '{{ $test->time }}',
                                '{{ $test->questionNumbers->first()->question_number ?? 'Không có' }}',
                                '{{ $test->user->name ?? 'Ẩn danh' }}',
                                '{{ $test->created_at->format('Y-m-d') }}'
                            )">
                                <div class="card h-100 p-3 shadow-sm border-0 rounded-4 card-3d">
                                    <div class="d-flex align-items-center">
                                        <img src="{{ asset('assets/img/test.jpg') }}" alt="Test icon"
                                            class="rounded-circle bg-primary p-1" width="50" height="50"
                                            style="object-fit: cover;">
                                        <div class="ms-3">
                                            <h5 class="fw-semibold mb-1 text-truncate">
                                                {{ optional($test->questionNumbers->first()->topic)->title ?? 'Không có chủ đề' }}
                                            </h5>
                                            <small class="text-muted d-block">⏱ Thời gian: {{ $test->time }} phút</small>
                                            <small class="text-muted d-block">❓ Số câu:
                                                {{ $test->questionNumbers->first()->question_number ?? 'Không có' }}</small>
                                            <small class="text-muted d-block">👤 Tác giả:
                                                {{ $test->user->name ?? 'Ẩn danh' }}</small>
                                            <small class="text-muted">📅 Ngày tạo:
                                                {{ $test->created_at->format('Y-m-d') }}</small>
                                        </div>
                                    </div>
                                </div>
                            </a>
                        </div>
                    </div>

                    {{-- Modal giao bài --}}
                    @if ($myClassrooms->isNotEmpty())
                        <div class="modal fade" id="assignModal_{{ $test->id }}" tabindex="-1"
                            aria-labelledby="assignModalLabel_{{ $test->id }}" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content border-0 rounded-3">
                                    <div class="modal-header bg-secondary text-white">
                                        <h5 class="modal-title">📚 Giao bài kiểm tra</h5>
                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                                            aria-label="Đóng"></button>
                                    </div>
                                    <form method="POST" action="{{ route('teacher.assignTest') }}">
                                        @csrf
                                        <input type="hidden" name="test_id" value="{{ $test->id }}">
                                        <div class="modal-body">
                                            <div class="mb-3">
                                                <label class="form-label fw-semibold">Chọn lớp học:</label>
                                                <div class="mb-2" style="max-height: 150px; overflow-y: auto;">
                                                    @foreach ($myClassrooms as $classroom)
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="checkbox"
                                                                name="classroom_ids[]" value="{{ $classroom->id }}"
                                                                id="classroom_modal_{{ $test->id }}_{{ $classroom->id }}">
                                                            <label class="form-check-label small"
                                                                for="classroom_modal_{{ $test->id }}_{{ $classroom->id }}">
                                                                {{ $classroom->name }} ({{ $classroom->code }})
                                                            </label>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                            <div class="mb-3">
                                                <label for="deadline_{{ $test->id }}" class="form-label">📅 Hạn
                                                    nộp:</label>
                                                <input type="datetime-local" name="deadline"
                                                    id="deadline_{{ $test->id }}" class="form-control">
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-outline-secondary"
                                                data-bs-dismiss="modal">Hủy</button>
                                            <button type="submit" class="btn btn-primary">✅ Giao bài</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @endif
                @endforeach
            </div>
        @endif
    </div>
@endcan
