@extends('user.master')

@section('title', 'Thông báo')

@section('content')
    <div class="container mt-4">
        <h2 class="fw-bold mb-3">🔔 Thông báo của bạn</h2>

        @if ($notifications->count())
            <div class="row">
                @foreach ($notifications as $note)
                    <div class="col-md-6 mb-3">
                        <div class="card {{ !$note->is_read ? 'bg-light border-primary' : '' }}">
                            <div class="card-body">
                                <h6 class="card-title {{ !$note->is_read ? 'fw-bold text-primary' : 'text-muted' }}">
                                    {!! $note->title ?? 'Thông báo' !!}
                                </h6>
                                <p class="card-text small text-muted mb-1">
                                    {{ $note->message }}
                                </p>
                                <p class="card-text text-end small text-gray-600">
                                    {{ $note->created_at->diffForHumans() }}
                                </p>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="mt-3">
                {{ $notifications->links() }}
            </div>
        @else
            <div class="alert alert-info">Không có thông báo nào.</div>
        @endif
    </div>
@endsection
