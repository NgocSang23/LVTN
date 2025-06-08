@extends('user.master')

@section('title', 'Tìm cặp flashcard')

@section('content')
    <style>
        .game-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            /* 3 cột */
            gap: 10px;
            /* Khoảng cách giữa các thẻ */
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

        .question-btn:hover {
            background-color: #007bff;
            color: white;
            border-color: #0056b3;
        }

        .answer-btn {
            background-color: #e7ffe7;
            color: #495057;
        }

        .answer-btn:hover {
            background-color: #28a745;
            color: white;
            border-color: #218838;
        }

        .matched {
            background-color: #28a745 !important;
            color: white !important;
            border-color: #218838 !important;
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
    </style>

    <div class="container text-center mt-4 d-flex flex-column" style="min-height: 90vh;">
        {{-- Thanh điều hướng và tiêu đề --}}
        <div class="d-flex align-items-center mb-3">
            <a href="{{ route('user.dashboard') }}" class="btn btn-outline-primary d-flex align-items-center me-3">
                <span class="me-2">🔙</span> Trở lại
            </a>
            <button class="btn btn-primary d-flex align-items-center" onclick="window.location.reload();">
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
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            let selected = []; // Danh sách các nút đang được chọn
            let matchedPairs = 0; // Số cặp đã ghép đúng
            const totalPairs = document.querySelectorAll('.match-btn').length / 2;

            // Gắn sự kiện click cho tất cả các nút flashcard
            document.querySelectorAll('.match-btn').forEach(btn => {
                btn.addEventListener('click', function() {
                    // Nếu nút đã chọn hoặc đã trùng khớp, bỏ qua
                    if (this.classList.contains('matched') || this.classList.contains('selected'))
                        return;

                    // Đánh dấu nút là đang chọn
                    this.classList.add('selected');
                    selected.push(this);

                    // Khi đã chọn đủ 2 nút
                    if (selected.length === 2) {
                        const [first, second] = selected;

                        const word1 = first.dataset.word;
                        const word2 = second.dataset.word;

                        // Nếu hai nút có cùng word (EN-VI là cặp)
                        if (word1 === word2) {
                            setTimeout(() => {
                                // Đánh dấu là đã ghép đúng và ẩn đi
                                first.classList.add('matched');
                                second.classList.add('matched');
                                first.classList.remove('selected');
                                second.classList.remove('selected');
                                first.classList.add('hidden');
                                second.classList.add('hidden');
                                selected = [];

                                matchedPairs++;

                                // Nếu đã ghép đủ tất cả các cặp
                                if (matchedPairs === totalPairs) {
                                    setTimeout(() => {
                                        // Hiện modal chúc mừng
                                        var myModal = new bootstrap.Modal(
                                            document.getElementById(
                                                'gameCompleteModal'));
                                        myModal.show();

                                        // Tự động tải lại sau 2 giây
                                        setTimeout(() => {
                                            window.location.reload();
                                        }, 2000);
                                    }, 2000);
                                }
                            }, 300);
                        } else {
                            // Nếu không đúng, hủy chọn sau 0.7s
                            setTimeout(() => {
                                first.classList.remove('selected');
                                second.classList.remove('selected');
                                selected = [];
                            }, 700);
                        }
                    }
                });
            });
        });
    </script>
@endsection
