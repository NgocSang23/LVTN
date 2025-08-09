@php
    $rawCardIds = $card_define['card_ids'] ?? [];
    if (!is_array($rawCardIds)) {
        $rawCardIds = explode(',', (string) $rawCardIds);
    }
    $cardIdsArray = array_filter($rawCardIds, fn($id) => !empty($id));
    $cardCount = count($cardIdsArray);
    $encodedIds = base64_encode(implode(',', $cardIdsArray));
@endphp

<div class="col-12 col-sm-6 col-lg-4">
    <div class="card h-100 p-3 shadow-sm border-0 rounded-4 card-3d position-relative"
        style="overflow: visible; z-index: 10;">
        <!-- Dropdown -->
        <div class="dropdown position-absolute top-0 end-0 p-3" style="z-index: 99999;">
            <span data-bs-toggle="dropdown" role="button"
                style="cursor: pointer; font-size: 20px; line-height: 1;">⋮</span>
            <ul class="dropdown-menu dropdown-menu-end show-on-top">
                <li class="dropdown-header text-muted">Chia sẻ</li>
                <li>
                    <a class="dropdown-item w-100 text-start" href="#"
                        onclick="copyToClipboard('{{ route('user.flashcard_define_essay', ['ids' => $encodedIds]) }}')">
                        📋 Sao chép liên kết
                    </a>
                </li>
                {{-- <li>
                    <a class="dropdown-item w-100 text-start" href="#"
                        onclick="showQrModal('{{ route('user.flashcard_define_essay', ['ids' => $encodedIds]) }}')">
                        🌐 Tạo mã QR
                    </a>
                </li> --}}
                @if (
                    !empty($card_define['first_card']->flashcardSet) &&
                        $card_define['first_card']->flashcardSet->is_public &&
                        $card_define['first_card']->flashcardSet->is_approved)
                    <li>
                        <a class="dropdown-item text-success w-100 text-start"
                            href="{{ route('flashcard.share', ['slug' => $card_define['first_card']->flashcardSet->slug]) }}">
                            🔗 Xem chia sẻ công khai
                        </a>
                    </li>
                @else
                    <li>
                        <form method="POST" action="{{ route('flashcard.share.create') }}">
                            @csrf
                            @foreach ($cardIdsArray as $id)
                                <input type="hidden" name="card_ids[]" value="{{ $id }}">
                            @endforeach
                            <button type="submit" class="dropdown-item text-primary w-100 text-start">
                                🌍 Chia sẻ công khai
                            </button>
                        </form>
                    </li>
                @endif
            </ul>
        </div>

        <!-- Nội dung -->
        <a href="{{ route('user.flashcard_define_essay', ['ids' => $encodedIds]) }}"
            class="text-decoration-none text-dark">
            <div class="d-flex align-items-center">
                <img src="{{ asset('assets/img/card_define.jpg') }}" alt="Icon"
                    class="rounded-circle bg-primary p-1" width="50" height="50" style="object-fit: cover;">
                <div class="ms-3">
                    <h5 class="mb-1 fw-semibold d-block text-truncate" style="max-width: 200px;">
                        {{ optional($card_define['first_card']->question->topic)->title ?? 'Không có chủ đề' }}
                    </h5>
                    <small class="text-muted d-block">📄 Số thẻ: {{ $cardCount }}</small>
                    <small class="text-muted d-block">👤 Tác giả:
                        {{ $card_define['first_card']->user->name ?? 'Ẩn danh' }}</small>
                    <small class="text-muted d-block">📅 Ngày tạo:
                        {{ $card_define['first_card']->created_at->format('Y-m-d') }}</small>
                </div>
            </div>
        </a>
    </div>
</div>
