<div class="mb-4">
    <h2 class="h4 mb-4">🧠 Bài kiểm tra</h2>
    <div class="row g-4">
        @forelse ($tests as $test)
            <div class="col-12 col-sm-6 col-lg-4">
                <div class="position-relative">
                    @if (auth()->check() && auth()->user()->roles === 'teacher' && count($myClassrooms) > 0)
                        <div class="dropdown position-absolute top-0 end-0 p-3" style="z-index: 1;">
                            <span data-bs-toggle="dropdown" role="button"
                                style="cursor: pointer; font-size: 20px; line-height: 1;">⋮</span>
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

                    <a href="javascript:void(0);" class="text-decoration-none text-dark"
                        onclick="showConfirmModal(
                                   '{{ $test->id }}',
                                   '{{ optional($test->questionNumbers->first()->topic)->title ?? 'Không có' }}',
                                   '{{ $test->time }}',
                                   '{{ $test->questionNumbers->first()->question_number ?? 'Không có' }}',
                                   '{{ $test->user->name ?? 'Ẩn danh' }}',
                                   '{{ $test->created_at->format('Y-m-d') }}'
                               )">
                        <div class="card h-100 p-3 shadow-sm border-0 rounded-4 card-3d" style="overflow: visible;">
                            <div class="d-flex align-items-center">
                                <img src="./assets/img/test.jpg" alt="Icon" class="rounded-circle bg-primary p-1"
                                    width="50" height="50" style="object-fit: cover;">
                                <div class="ms-3">
                                    <h5 class="mb-1 fw-semibold text-truncate">
                                        {{ optional($test->questionNumbers->first()->topic)->title ?? 'Không có' }}
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
        @empty
            <p class="text-muted">Chưa có bài kiểm tra nào được tạo.</p>
        @endforelse

        {{-- Modal giao bài --}}
        @foreach ($tests as $test)
            @if (auth()->check() && auth()->user()->roles === 'teacher' && count($myClassrooms) > 0)
                <div class="modal fade" id="assignModal_{{ $test->id }}" tabindex="-1"
                    aria-labelledby="assignModalLabel_{{ $test->id }}" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content border-0 rounded-3">
                            <div class="modal-header bg-secondary text-white">
                                <h5 class="modal-title" id="assignModalLabel_{{ $test->id }}">📚 Giao bài kiểm
                                    tra</h5>
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
                                        <input type="datetime-local" name="deadline" id="deadline_{{ $test->id }}"
                                            class="form-control">
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-outline-secondary"
                                        data-bs-dismiss="modal">Hủy</button>
                                    <button type="submit" class="btn btn-primary">✅ Chia sẻ</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            @endif
        @endforeach
    </div>
</div>
