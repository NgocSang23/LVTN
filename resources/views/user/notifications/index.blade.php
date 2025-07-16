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
                    @include('user.notifications.notification_card', ['note' => $note])
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
        function fetchNotifications() {
            fetch('/api/notifications/latest')
                .then(res => res.json())
                .then(data => {
                    // Cập nhật dropdown thông báo
                    const dropdown = document.querySelector('#notificationDropdown');
                    if (dropdown) dropdown.innerHTML = data.html;

                    // Cập nhật badge đếm
                    const badge = document.querySelector('.badge-counter');
                    if (badge) {
                        badge.textContent = data.unread;
                        badge.style.display = data.unread > 0 ? 'inline-block' : 'none';
                    }

                    // Nếu có danh sách đầy đủ đang hiển thị
                    const fullList = document.querySelector('#full-notification-list');
                    if (fullList) {
                        fullList.innerHTML = data.full_html;
                    }
                });
        }

        document.addEventListener('DOMContentLoaded', fetchNotifications);
        setInterval(fetchNotifications, 5000);
    </script>
@endsection
