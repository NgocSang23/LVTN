@extends('user.master')

@section('title', 'Xem bộ flashcard chia sẻ')

@section('content')
    <div class="container mt-4">
        <h2 class="fw-bold mb-1">{{ $set->title }}</h2>
        <p class="text-muted mb-4">{{ $set->description }}</p>

        <!-- Nút chức năng chia sẻ -->
        <div class="d-flex flex-wrap gap-2 mb-4">
            <button id="copyLinkBtn" class="btn btn-outline-primary position-relative">
                <i class="fas fa-copy"></i> Sao chép liên kết
                <span id="copiedBadge" class="badge bg-success ms-2 d-none">Đã sao chép!</span>
            </button>

            {{-- <button class="btn btn-outline-dark" data-bs-toggle="modal" data-bs-target="#qrModal">
                <i class="fas fa-qrcode"></i> Mã QR chia sẻ
            </button> --}}
            {{--
            <button class="btn btn-outline-primary" id="fbShareBtn">
                <i class="fab fa-facebook"></i> Chia sẻ Facebook
            </button>

            <button class="btn btn-outline-info" id="zaloShareBtn">
                <i class="fas fa-comment-alt"></i> Chia sẻ Zalo
            </button> --}}

            @if (count(explode(',', $set->question_ids)) > 4)
                <a href="{{ route('user.flashcard_define_essay', base64_encode($set->question_ids)) }}"
                    class="btn btn-success">
                    <i class="fas fa-play"></i> Bắt đầu ôn tập
                </a>
            @endif
        </div>

        <hr>

        <!-- Danh sách câu hỏi -->
        @forelse ($questions as $q)
            <div class="card mb-3 shadow-sm">
                <div class="card-body">
                    <h5 class="card-title">{{ $q->content }}</h5>

                    @if ($q->images && count($q->images))
                        <img src="{{ asset('storage/' . $q->images[0]->path) }}" class="img-fluid rounded mt-2"
                            style="max-width: 300px;">
                    @endif

                    <p class="card-text mt-2">
                        <strong>Đáp án:</strong> {{ $q->answers[0]->content ?? 'Chưa có đáp án' }}
                    </p>
                </div>
            </div>
        @empty
            <p class="text-muted">Không có câu hỏi nào trong bộ flashcard này.</p>
        @endforelse

        <!-- Modal QR -->
        <div class="modal fade" id="qrModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content p-4 text-center">
                    <h5 class="mb-3 fw-semibold">📷 Mã QR chia sẻ</h5>
                    <div id="qrcode" class="d-flex justify-content-center mb-3"></div>
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Đóng</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('js')
    <script src="https://cdn.jsdelivr.net/npm/qrcodejs@1.0.0/qrcode.min.js"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const copyBtn = document.getElementById("copyLinkBtn");
            const copiedBadge = document.getElementById("copiedBadge");

            if (copyBtn && copiedBadge) {
                copyBtn.addEventListener("click", function() {
                    const startStudyBtn = document.querySelector('a.btn.btn-success');
                    if (startStudyBtn) {
                        const linkToCopy = startStudyBtn.href;

                        navigator.clipboard.writeText(linkToCopy).then(() => {
                            copiedBadge.classList.remove("d-none");
                            setTimeout(() => {
                                copiedBadge.classList.add("d-none");
                            }, 2000);
                        }).catch(console.error);
                    }
                });
            }

            // QR Code khi mở modal
            // const qrModal = document.getElementById("qrModal");
            // if (qrModal) {
            //     qrModal.addEventListener("shown.bs.modal", function() {
            //         const qrContainer = document.getElementById("qrcode");
            //         qrContainer.innerHTML = "";
            //         new QRCode(qrContainer, {
            //             text: fullUrl,
            //             width: 180,
            //             height: 180
            //         });
            //     });
            // }

            // // Chia sẻ Facebook
            // document.getElementById("fbShareBtn")?.addEventListener("click", function() {
            //     const fbUrl = `https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(fullUrl)}`;
            //     window.open(fbUrl, '_blank', 'width=600,height=500');
            // });

            // // Chia sẻ Zalo
            // document.getElementById("zaloShareBtn")?.addEventListener("click", function() {
            //     const zaloUrl = `https://zalo.me/share?url=${encodeURIComponent(fullUrl)}`;
            //     window.open(zaloUrl, '_blank');
            // });
        });
    </script>
@endsection
