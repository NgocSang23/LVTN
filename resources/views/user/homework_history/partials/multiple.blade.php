<div class="mb-4">
    <h2 class="h4 mb-4">🧠 Bài kiểm tra đã làm</h2>
    <div class="row g-4">
        @forelse ($multiple_data as $item)
            <div class="col-12 col-sm-6 col-lg-4">
                <div class="position-relative">
                    <a href="{{ route('user.history_multiple_choice_detail', $item->id_de_thi) }}"
                        class="text-decoration-none text-dark">
                        <div class="card h-100 p-3 shadow-sm border-0 rounded-4 card-3d" style="overflow: visible;">
                            <div class="d-flex align-items-center">
                                <img src="/assets/img/test.jpg" alt="Icon" class="rounded-circle bg-primary p-1"
                                    width="50" height="50" style="object-fit: cover;">
                                <div class="ms-3">
                                    <h5 class="mb-1 fw-semibold text-truncate">
                                        {{ $item->ten_de_thi ?? 'Không có tên' }}
                                    </h5>
                                    <small class="text-muted d-block">
                                        ✅ Số câu đúng: {{ $item->so_cau_dung }} / {{ $item->tong_so_cau }}
                                    </small>
                                    <small class="text-muted d-block">📊 Điểm: {{ $item->diem }}</small>
                                    <small class="text-muted d-block">⏱ Thời gian: {{ $item->thoi_gian }}</small>
                                    <small class="text-muted d-block">👤 Người tạo: {{ $item->nguoi_tao }}</small>
                                </div>
                            </div>
                        </div>
                    </a>

                    <div class="d-flex justify-content-end mt-2">
                        <a href="{{ route('user.history_multiple_choice_detail', $item->id_de_thi) }}"
                            class="btn btn-sm btn-outline-primary me-2">📄 Xem chi tiết</a>
                        <a href="{{ route('flashcard_multiple_choice.show', $item->id_de_thi) }}"
                            class="btn btn-sm btn-outline-secondary">🔁 Làm lại</a>
                    </div>
                </div>
            </div>
        @empty
            <p class="text-muted">Chưa làm bài kiểm tra nào.</p>
        @endforelse
    </div>
</div>
