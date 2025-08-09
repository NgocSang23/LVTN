@extends('user.master')

@section('title', 'Kết quả học tập')

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

    @php
        $activeTab = request('tab', 'define');
    @endphp

    <div class="container py-4">
        <h1 class="mb-4">📊 Kết quả học tập của bạn</h1>

        <div class="row g-3 mb-4">
            <div class="col-md-3">
                <div class="card shadow-sm border-0 rounded-4 p-3 text-center">
                    <h6>Thẻ đã học</h6>
                    <h4>{{ $stats['learnedCards'] }} / {{ $stats['totalCards'] }}</h4>
                    <small>{{ $stats['completionRate'] }}% hoàn thành</small>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card shadow-sm border-0 rounded-4 p-3 text-center">
                    <h6>Bài kiểm tra</h6>
                    <h4>{{ $stats['totalTests'] }}</h4>
                    <small>Điểm TB: {{ $stats['avgScore'] }}</small>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card shadow-sm border-0 rounded-4 p-3 text-center">
                    <h6>Độ chính xác</h6>
                    <h4>{{ $stats['accuracyRate'] }}%</h4>
                    <small>T/g trung bình: {{ $stats['avgTimePerQuestion'] }} giây/câu</small>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card shadow-sm border-0 rounded-4 p-3 text-center">
                    <h6>Thẻ khó</h6>
                    <h4>{{ $stats['difficultCount'] }}</h4>
                    <small>Cần ôn lại</small>
                </div>
            </div>
        </div>

        <!-- Tabs -->
        <ul class="nav nav-tabs mb-4">
            <li class="nav-item">
                <a class="nav-link {{ $activeTab === 'define' ? 'active' : '' }}"
                    href="{{ route('user.history', ['tab' => 'define']) }}">
                    Các khái niệm đã học
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ $activeTab === 'multiple' ? 'active' : '' }}"
                    href="{{ route('user.history', ['tab' => 'multiple']) }}">
                    Các bài kiểm tra đã làm
                </a>
            </li>
        </ul>

        <!-- Bộ lọc -->
        <form method="GET" class="row g-3 mb-4">
            <input type="hidden" name="tab" value="{{ $activeTab }}">
            <div class="col-md-3">
                <select name="sort" class="form-select">
                    <option value="all" {{ request('sort') == 'all' ? 'selected' : '' }}>Tất cả</option>
                    <option value="new" {{ request('sort') == 'new' ? 'selected' : '' }}>Mới nhất</option>
                    <option value="old" {{ request('sort') == 'old' ? 'selected' : '' }}>Cũ nhất</option>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100">Lọc</button>
            </div>
        </form>

        @if ($activeTab === 'define')
            @include('user.homework_history.partials.define', ['define_data' => $define_data])
        @elseif ($activeTab === 'multiple')
            @include('user.homework_history.partials.multiple', ['multiple_data' => $multiple_data])
        @endif
    </div>
@endsection
