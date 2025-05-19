@extends('user.master')

@section('title', 'Thư viện')

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
        <h1 class="mb-4">Thư viện của bạn</h1>
        <ul class="nav nav-tabs mb-4">
            <li class="nav-item">
                <a class="nav-link active" href="{{ route('user.library_define_essay') }}">Các khái niệm - Các câu tự luận</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="{{ route('user.library_multiple') }}">Các bài kiểm tra</a>
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
        <div class="mb-5">
            <div class="row g-3">
                @forelse ($card_defines as $card_define)
                    <div class="col-12 col-sm-6 col-lg-4">
                        <a href="{{ route('user.flashcard_define_essay', ['ids' => implode(',', (array) $card_define['card_ids'])]) }}"
                            class="text-decoration-none text-dark">
                            <div class="card h-100 p-3 shadow-sm border-0 rounded-4 card-3d">
                                <div class="d-flex align-items-center">
                                    <img src="./assets/img/card_define.jpg" alt="Icon"
                                        class="rounded-circle bg-primary p-1" width="50">
                                    <div class="ms-3">
                                        <h5 class="h6 mb-1">
                                            {{ $card_define['first_card']->question->topic->title ?? 'Không có chủ đề' }}
                                        </h5>
                                        <p class="text-muted mb-1">
                                            Số thẻ:
                                            {{ $card_define['first_card']->question->topic->questions->count() ?? 0 }} |
                                            Tác giả:
                                            {{ $card_define['first_card']->user->name ?? 'Ẩn danh' }}
                                        </p>
                                        <p class="text-muted mb-0">
                                            Ngày tạo: {{ $card_define['first_card']->created_at->format('Y-m-d') }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>
                @empty
                    <p class="text-muted">Chưa có thẻ nào được tạo.</p>
                @endforelse
            </div>
        </div>

        <h3>Câu hỏi tự luận</h3>
        <div class="mb-5">
            <div class="row g-3">
                @forelse ($card_essays as $card_essay)
                    <div class="col-12 col-sm-6 col-lg-4">
                        <a href="{{ route('user.flashcard_define_essay', ['ids' => implode(',', (array) $card_essay['card_ids'])]) }}"
                            class="text-decoration-none text-dark">
                            <div class="card h-100 p-3 shadow-sm border-0 rounded-4 card-3d">
                                <div class="d-flex align-items-center">
                                    <img src="./assets/img/card_essay.jpg" alt="Icon"
                                        class="rounded-circle bg-primary p-1" width="50">
                                    <div class="ms-3">
                                        <h5 class="h6 mb-1">
                                            {{ $card_essay['first_card']->question->topic->title ?? 'Không có chủ đề' }}
                                        </h5>
                                        <p class="text-muted mb-1">
                                            Số thẻ:
                                            {{ $card_essay['first_card']->question->topic->questions->count() ?? 0 }} |
                                            Tác giả:
                                            {{ $card_essay['first_card']->user->name ?? 'Ẩn danh' }}
                                        </p>
                                        <p class="text-muted mb-0">
                                            Ngày tạo: {{ $card_essay['first_card']->created_at->format('Y-m-d') }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>
                @empty
                    <p class="text-muted">Chưa có thẻ nào được tạo.</p>
                @endforelse
            </div>
        </div>
    </div>
@endsection
