@extends('user.master')

@section('title', 'Thông báo')

@section('content')
    <div class="container mt-4">
        <h2 class="fw-bold mb-3">🔔 Thông báo của bạn</h2>

        @if ($notifications->count())
            <div class="d-flex justify-content-between align-items-center mb-2">
                <form method="POST" action="{{ route('notifications.deleteAll') }}">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-sm btn-danger">Xoá tất cả</button>
                </form>
            </div>

            <div class="row row-cols-1 row-cols-md-2 g-3">
                @foreach ($notifications as $note)
                    <div class="col">
                        <div class="card shadow-sm {{ !$note->is_read ? 'border-warning' : '' }}">
                            <div class="card-body d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="card-title mb-1 fw-bold">{!! $note->title !!}</h6>
                                    <p class="card-text small text-muted mb-1">{{ $note->message }}</p>
                                    <small class="text-secondary">{{ $note->created_at->diffForHumans() }}</small>
                                </div>
                                <div class="d-flex flex-column align-items-end">
                                    @if ($note->url)
                                        <a href="{{ $note->url }}" class="btn btn-sm btn-outline-primary mb-2">Xem</a>
                                    @endif
                                    <form method="POST" action="{{ route('notifications.delete', $note->id) }}">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger">Xoá</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="mt-4">
                {{ $notifications->links() }}
            </div>
        @else
            <div class="alert alert-info">Không có thông báo nào.</div>
        @endif
    </div>
@endsection

@section('scripts')
    <script>
        // Hàm này sẽ tự động gọi API mỗi 5 giây để cập nhật dropdown thông báo và số badge đỏ
        function fetchNotifications() {
            fetch('/api/notifications/latest')
                .then(res => res.json())
                .then(data => {
                    // Cập nhật phần dropdown thông báo
                    const dropdown = document.querySelector('#notificationDropdown');
                    if (dropdown) dropdown.innerHTML = data.html;

                    // Cập nhật badge đếm số thông báo chưa đọc
                    const badge = document.querySelector('.badge-counter');
                    if (badge) {
                        badge.textContent = data.unread;
                        badge.style.display = data.unread > 0 ? 'inline-block' : 'none';
                    }
                });
        }

        // Gọi lần đầu khi trang vừa tải và lặp lại mỗi 5 giây
        document.addEventListener('DOMContentLoaded', fetchNotifications);
        setInterval(fetchNotifications, 5000);
    </script>
@endsection
