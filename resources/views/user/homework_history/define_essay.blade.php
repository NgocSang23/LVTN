@extends('user.master')

@section('title', 'Kết quả học tập')

@section('content')
    <style>
        .card-3d {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            transform-style: preserve-3d;
            will-change: transform;
        }

        .card-3d:hover {
            transform: perspective(1000px) rotateX(5deg) rotateY(5deg) scale(1.02);
            box-shadow: 0 12px 20px rgba(0, 0, 0, 0.15);
        }
    </style>
    <div class="container py-4">
        <h1 class="mb-4">Kết quả học tập của bạn</h1>
        <ul class="nav nav-tabs mb-4">
            <li class="nav-item">
                <a class="nav-link active" href="{{ route('user.history_define_essay') }}">Các khái niệm - tự luận đã học</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="{{ route('user.history_multiple_choice') }}">Các bài kiểm tra đã làm</a>
            </li>
        </ul>
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div class="dropdown">
                <button class="btn btn-outline-secondary dropdown-toggle" type="button" id="dropdownMenuButton"
                    data-bs-toggle="dropdown" aria-expanded="false">
                    Gần đây
                </button>
                <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                    <li><a class="dropdown-item" href="#">Action</a></li>
                    <li><a class="dropdown-item" href="#">Another action</a></li>
                    <li><a class="dropdown-item" href="#">Something else here</a></li>
                </ul>
            </div>
            <div class="input-group w-50">
                <input type="text" class="form-control rounded-pill" placeholder="Tìm kiếm thẻ ghi nhớ">
                <span class="input-group-text bg-white border-0"><i class="fas fa-search"></i></span>
            </div>
        </div>

        <h3>Khái niệm / Định nghĩa</h3>
        <div class="mb-5 mt-3">
            <div class="row g-3">
                @forelse ($define_data as $item)
                    <div class="col-12 col-sm-6 col-lg-4">
                        <a href="{{ route('user.flashcard_define_essay', ['ids' => $item->card_ids]) }}"
                            class="text-decoration-none">
                            <div class="card h-100 p-3 shadow-sm border-0 rounded-4 card-3d">
                                <div class="d-flex align-items-center">
                                    <img src="./assets/img/card_define.jpg" alt="Icon"
                                        class="rounded-circle bg-primary p-1" width="50">
                                    <div class="ms-3">
                                        <h5 class="h6 mb-1">Môn học:
                                            <strong>{{ $item->ten_mon_hoc ?? 'Không có môn học' }}</strong>
                                        </h5>
                                        <h5 class="h6 mb-1">Chủ đề:
                                            <strong>{{ $item->ten_chu_de ?? 'Không có chủ đề' }}</strong>
                                        </h5>
                                        <p class="text-muted mb-1"> Số thẻ đã học:
                                            <strong>{{ $item->tong_so_the_da_hoc }} / {{ $item->tong_so_the }}</strong>
                                        </p>
                                        <p class="text-muted"> Người tạo:
                                            <strong>{{ $item->nguoi_tao }}</strong>
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>
                @empty
                    <p class="text-muted">Chưa học thẻ nào</p>
                @endforelse
            </div>
        </div>

        <h3>Câu hỏi tự luận</h3>
        <div class="mb-5 mt-3">
            <div class="row g-3">
                @forelse ($essay_data as $item)
                    <div class="col-12 col-sm-6 col-lg-4">
                        <a href="{{ route('user.flashcard_define_essay', ['ids' => $item->card_ids]) }}"
                            class="text-decoration-none">
                            <div class="card h-100 p-3 shadow-sm border-0 rounded-4 card-3d">
                                <div class="d-flex align-items-center">
                                    <img src="./assets/img/card_essay.jpg" alt="Icon"
                                        class="rounded-circle bg-primary p-1" width="50">
                                    <div class="ms-3">
                                        <h5 class="h6 mb-1">Môn học:
                                            <strong>{{ $item->ten_mon_hoc ?? 'Không có môn học' }}</strong>
                                        </h5>
                                        <h5 class="h6 mb-1">Chủ đề:
                                            <strong>{{ $item->ten_chu_de ?? 'Không có chủ đề' }}</strong>
                                        </h5>
                                        <p class="text-muted mb-1"> Số thẻ đã học:
                                            <strong>{{ $item->tong_so_the_da_hoc }} / {{ $item->tong_so_the }}</strong>
                                        </p>
                                        <p class="text-muted"> Người tạo:
                                            <strong>{{ $item->nguoi_tao }}</strong>
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>
                @empty
                    <p class="text-muted">Chưa học thẻ nào</p>
                @endforelse
            </div>
        </div>
    </div>
@endsection
