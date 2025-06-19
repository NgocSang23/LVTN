@extends('user.master')

@section('title', 'Tìm cặp flashcard')

@section('content')
    <style>
        .game-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 10px;
            margin-top: 20px;
        }

        .match-btn {
            font-size: 1.2rem;
            font-weight: 500;
            transition: all 0.3s ease-in-out;
            border: 2px solid #ddd;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100px;
            text-align: center;
        }

        .question-btn {
            background-color: #f0f8ff;
            color: #495057;
        }

        .question-btn:hover,
        .answer-btn:hover {
            border-color: #000;
        }

        .answer-btn {
            background-color: #f0f8ff;
            color: orange;
        }

        .hidden {
            display: none;
        }

        .reload-btn {
            margin-top: 20px;
            font-size: 1rem;
            padding: 10px 20px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        .reload-btn:hover {
            background-color: #0056b3;
        }

        .pagination-container .pagination {
            margin-bottom: 0;
        }

        .pagination .page-link {
            border-radius: 50px !important;
            margin: 0 3px;
            transition: all 0.2s;
        }

        .pagination .page-item.active .page-link {
            background-color: #007bff;
            border-color: #007bff;
            color: white;
        }

        .pagination .page-link:hover {
            background-color: #e9ecef;
        }

        .pagination .pagination-summary {
            display: none;
        }

        .dropdown-menu-match {
            background-color: #0d1117;
            color: white;
            min-width: 220px;
        }

        .dropdown-item-match {
            color: white;
            padding: 10px 15px;
            transition: 0.2s;
        }

        .dropdown-item-match:hover {
            background-color: #161b22;
            color: white;
        }

        .dropdown-item-match i {
            font-size: 1rem;
        }

        .dropdown-toggle-match {
            background-color: #0d1117;
            color: white;
            border: none;
        }

        .dropdown-toggle-match:hover {
            background-color: #161b22;
        }

        .dropdown-divider-match {
            border-top: 1px solid #30363d;
        }

        .match-btn {
            transition: all 0.2s ease-in-out;
        }
    </style>

    <div class="container text-center mt-4 d-flex flex-column" style="min-height: 90vh;">
        {{-- Thanh điều hướng và tiêu đề --}}
        <div class="d-flex align-items-center mb-3">
            <div class="dropdown">
                <button class="btn btn-dark dropdown-toggle-match d-flex align-items-center rounded-3" type="button"
                    id="flashcardMenu" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="fas fa-clone text-primary"></i>
                    <span class="mx-2">Chọn chế độ học</span>
                </button>
                <ul class="dropdown-menu dropdown-menu-match shadow-lg rounded-3 border-0 mt-2"
                    aria-labelledby="flashcardMenu">
                    @php
                        $encodedIds = base64_encode(implode(',', $idsArray));
                    @endphp
                    <li>
                        <a class="dropdown-item dropdown-item-match d-flex align-items-center gap-2"
                            href="{{ route('game.match', ['ids' => $encodedIds]) }}">
                            <i class="fas fa-sync-alt text-primary"></i> Tìm cặp
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item dropdown-item-match d-flex align-items-center gap-2"
                            href="{{ route('game.study', ['ids' => $encodedIds]) }}">
                            <i class="fas fa-file-alt text-primary"></i> Học tập
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item dropdown-item-match d-flex align-items-center gap-2"
                            href="{{ route('game.check', ['ids' => $encodedIds]) }}">
                            <i class="fas fa-layer-group text-primary"></i> Kiểm tra
                        </a>
                    </li>
                    <li>
                        <hr class="dropdown-divider-match">
                    </li>
                    <li>
                        <a class="dropdown-item dropdown-item-match d-flex align-items-center gap-2"
                            href="{{ route('user.dashboard') }}">
                            <i class="fas fa-home text-primary"></i> Trang chủ
                        </a>
                    </li>
                </ul>
            </div>
            <button class="btn btn-primary d-flex align-items-center mx-2" onclick="window.location.reload();">
                Tải lại trò chơi
            </button>
        </div>
        <h2 class="fw-bold text-secondary">Tìm cặp</h2>

        {{-- PHP xử lý dữ liệu từ controller --}}
        @php
            $cards = [];
            foreach ($pairs as $item) {
                $cards[] = ['type' => 'question', 'text' => $item['question'], 'match' => $item['vi']];
                $cards[] = ['type' => 'answer', 'text' => $item['vi'], 'match' => $item['vi']];
            }

            shuffle($cards);
        @endphp

        {{-- Phần game grid (nội dung chiếm phần còn lại) --}}
        <div class="flex-grow-1">
            <div class="game-grid">
                @foreach ($cards as $card)
                    <div class="grid-item">
                        <button
                            class="btn match-btn w-100 p-3 shadow-sm rounded-3 {{ $card['type'] === 'question' ? 'question-btn' : 'answer-btn' }}"
                            data-question="{{ $card['type'] === 'question' ? $card['text'] : '' }}"
                            data-word="{{ $card['match'] }}" style="font-size: 16px;">
                            {{ $card['text'] }}
                        </button>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Phân trang luôn ở cuối --}}
        @if ($pairs->hasPages())
            <div class="m-4 d-flex justify-content-between align-items-center flex-wrap gap-3">
                <div class="text-muted small fst-italic">
                    <span>Trang <strong>{{ $pairs->currentPage() }}</strong> /
                        <strong>{{ $pairs->lastPage() }}</strong></span>
                    &mdash;
                    <span>Tổng <strong>{{ $pairs->total() }}</strong> cặp</span>
                </div>
                <div class="pagination-container shadow-sm rounded-3 px-3 py-1 bg-white">
                    {{ $pairs->links('pagination::bootstrap-5') }}
                </div>
            </div>
        @endif
    </div>

    {{-- Modal hiển thị khi hoàn thành trò chơi --}}
    <div class="modal fade" id="gameCompleteModal" tabindex="-1" aria-labelledby="gameCompleteModalLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="gameCompleteModalLabel">Chúc mừng!</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    Bạn đã tìm tất cả các cặp từ thành công. Trò chơi sẽ tự động tải lại sau 2 giây.
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Đóng</button>
                </div>
            </div>
        </div>
    </div>

    {{-- Script JavaScript xử lý logic trò chơi --}}
    <script src="{{ asset('js/game_match.js') }}"></script>
@endsection
