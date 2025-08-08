@extends('user.master')

@section('title', 'Thư viện')

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
        $activeTab = request('tab', 'define_essay'); // Mặc định là khái niệm
    @endphp

    <div class="container py-4">
        <h1 class="mb-4">Thư viện của bạn</h1>

        <!-- Tabs -->
        <ul class="nav nav-tabs mb-4">
            <li class="nav-item">
                <a class="nav-link {{ $activeTab === 'define_essay' ? 'active' : '' }}"
                    href="{{ route('user.library', ['tab' => 'define_essay']) }}">
                    Các khái niệm
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ $activeTab === 'multiple' ? 'active' : '' }}"
                    href="{{ route('user.library', ['tab' => 'multiple']) }}">
                    Các bài kiểm tra
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

        @if ($activeTab === 'define_essay')
            @include('user.library.partials.define_essay', ['card_defines' => $card_defines])
        @elseif ($activeTab === 'multiple')
            @include('user.library.partials.multiple', [
                'tests' => $tests,
                'myClassrooms' => $myClassrooms,
            ])
        @endif
    </div>
@endsection
