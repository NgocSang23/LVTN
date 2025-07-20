<div class="mb-4">
    <h2 class="h4 mb-4">📘 Khái niệm / định nghĩa</h2>
    <div class="row g-4">
        @forelse ($card_defines as $card_define)
            <div class="col-12 col-sm-6 col-lg-4">
                <div class="position-relative">
                    <!-- Dropdown chia sẻ -->
                    <div class="dropdown position-absolute top-0 end-0 p-3" style="z-index: 1;">
                        <span data-bs-toggle="dropdown" role="button"
                            style="cursor: pointer; font-size: 20px; line-height: 1;">⋮</span>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li class="dropdown-header text-muted">Chia sẻ</li>
                            <li>
                                <a class="dropdown-item" href="#"
                                    onclick="event.preventDefault(); copyToClipboard('{{ route('user.flashcard_define_essay', ['ids' => implode(',', (array) $card_define['card_ids'])]) }}')">📋
                                    Sao chép liên kết</a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="#"
                                    onclick="event.preventDefault(); showQrModal('{{ route('user.flashcard_define_essay', ['ids' => implode(',', (array) $card_define['card_ids'])]) }}')">🌐
                                    Tạo mã QR</a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="#"
                                    onclick="event.preventDefault(); shareFacebook('{{ route('user.flashcard_define_essay', ['ids' => implode(',', (array) $card_define['card_ids'])]) }}')">📤
                                    Chia sẻ Facebook</a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="#"
                                    onclick="event.preventDefault(); shareZalo('{{ route('user.flashcard_define_essay', ['ids' => implode(',', (array) $card_define['card_ids'])]) }}')">💬
                                    Chia sẻ Zalo</a>
                            </li>

                            @if (empty($card_define['first_card']->flashcardSet?->slug))
                                <form method="POST" action="{{ route('flashcard.share.create') }}" class="px-2">
                                    @csrf
                                    @foreach (explode(',', $card_define['card_ids']) as $id)
                                        <input type="hidden" name="card_ids[]" value="{{ $id }}">
                                    @endforeach
                                    <button type="submit" class="dropdown-item text-primary w-100 text-start">
                                        🌍 Chia sẻ công khai
                                    </button>
                                </form>
                            @else
                                <li>
                                    <a class="dropdown-item text-success"
                                        href="{{ route('flashcard.share', ['slug' => $card_define['first_card']->flashcardSet->slug]) }}">🔗
                                        Xem chia sẻ công khai</a>
                                </li>
                            @endif
                        </ul>
                    </div>

                    <!-- Nội dung thẻ -->
                    <a href="{{ route('user.flashcard_define_essay', ['ids' => implode(',', (array) $card_define['card_ids'])]) }}"
                        class="text-decoration-none text-dark">
                        <div class="card h-100 p-3 shadow-sm border-0 rounded-4 card-3d" style="overflow: visible;">
                            <div class="d-flex align-items-center">
                                <img src="./assets/img/card_define.jpg" alt="Icon"
                                    class="rounded-circle bg-primary p-1" width="50" height="50"
                                    style="object-fit: cover;">
                                <div class="ms-3">
                                    <h5 class="mb-1 fw-semibold text-truncate">
                                        {{ optional($card_define['first_card']->question->topic)->title ?? 'Không có chủ đề' }}
                                    </h5>
                                    <small class="text-muted d-block">
                                        📄 Số thẻ: {{ count(explode(',', $card_define['card_ids'])) }}
                                    </small>
                                    <small class="text-muted d-block">
                                        👤 Tác giả: {{ $card_define['first_card']->user->name ?? 'Ẩn danh' }}
                                    </small>
                                    <small class="text-muted">
                                        📅 Ngày tạo: {{ $card_define['first_card']->created_at->format('Y-m-d') }}
                                    </small>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>
            </div>
        @empty
            <p class="text-muted">Chưa có thẻ nào được tạo.</p>
        @endforelse
    </div>
</div>

{{-- Modal sao chép liên kết --}}
<div class="modal fade" id="copySuccessModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content text-center p-4">
            <h5 class="mb-2 text-success"><i class="fas fa-check-circle"></i> Đã sao chép liên kết</h5>
            <p class="text-muted mb-0">Liên kết đã được sao chép vào clipboard.</p>
        </div>
    </div>
</div>

{{-- Modal mã QR --}}
<div class="modal fade" id="qrModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content text-center p-4">
            <h5 class="mb-3">🌐 Mã QR chia sẻ</h5>
            <div class="d-flex justify-content-center">
                <div id="qrcode-container"></div>
            </div>
            <button class="btn btn-secondary mt-3" data-bs-dismiss="modal">Đóng</button>
        </div>
    </div>
</div>

<script>
    function copyToClipboard(link) {
        navigator.clipboard.writeText(link).then(() => {
            const copyModal = new bootstrap.Modal(document.getElementById('copySuccessModal'));
            copyModal.show();
            setTimeout(() => copyModal.hide(), 2500);
        }).catch(err => {
            console.error("❌ Không thể sao chép liên kết: ", err);
        });
    }

    function showQrModal(link) {
        const qrContainer = document.getElementById("qrcode-container");
        qrContainer.innerHTML = "";
        new QRCode(qrContainer, {
            text: link,
            width: 200,
            height: 200
        });

        const qrModal = new bootstrap.Modal(document.getElementById('qrModal'));
        qrModal.show();
    }

    function shareFacebook(link) {
        const url = `https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(link)}`;
        window.open(url, '_blank', 'width=600,height=500');
    }

    function shareZalo(link) {
        const zaloUrl = `https://zalo.me/share?url=${encodeURIComponent(link)}`;
        window.open(zaloUrl, '_blank');
    }
</script>

<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
