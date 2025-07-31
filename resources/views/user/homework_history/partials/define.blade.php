<h3 class="mb-3">📘 Khái niệm / Định nghĩa</h3>
<div class="row g-3">
    @forelse ($define_data as $item)
        @php
            $encodedIds = base64_encode($item->card_ids);
        @endphp
        <div class="col-12 col-sm-6 col-lg-4">
            <a href="{{ route('user.flashcard_define_essay', ['ids' => $encodedIds]) }}"
                class="text-decoration-none text-dark">
                <div class="card h-100 p-3 shadow-sm border-0 rounded-4 card-3d">
                    <div class="d-flex align-items-center">
                        <img src="/assets/img/card_define.jpg" class="rounded-circle bg-primary p-1" width="50"
                            alt="icon">
                        <div class="ms-3">
                            <h6 class="mb-1">Môn học: <strong>{{ $item->ten_mon_hoc ?? 'Không có' }}</strong></h6>
                            <h6 class="mb-1">Chủ đề: <strong>{{ $item->ten_chu_de ?? 'Không có' }}</strong></h6>
                            <p class="text-muted mb-1">Số thẻ đã học: <strong>{{ $item->tong_so_the_da_hoc }} /
                                    {{ $item->tong_so_the }}</strong></p>
                            <p class="text-muted">Người tạo: <strong>{{ $item->nguoi_tao }}</strong></p>
                        </div>
                    </div>
                </div>
            </a>
        </div>
    @empty
        <p class="text-muted">Chưa học thẻ nào.</p>
    @endforelse
</div>
