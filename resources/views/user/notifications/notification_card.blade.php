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
