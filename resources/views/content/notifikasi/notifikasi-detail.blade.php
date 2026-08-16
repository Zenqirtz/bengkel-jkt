@extends('layouts/layoutMaster')

@section('title', 'Semua Notifikasi')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="card">
        <div class="card-header d-flex align-items-center justify-content-between">
            <h5 class="mb-0">Semua Notifikasi</h5>
            <a href="javascript:void(0)" id="btn-read-all-page"
               class="btn btn-sm btn-outline-primary">
                <i class="ri ri-mail-open-line me-1"></i> Baca Semua
            </a>
        </div>
        <div class="card-body p-0">
            <ul class="list-group list-group-flush">
                @forelse ($notifikasi as $n)
                    <li class="list-group-item list-group-item-action py-3 px-4
                        {{ $n->is_read ? '' : 'bg-label-primary' }}"
                        style="cursor: pointer;"
                        onclick="{{ $n->url ? "window.location.href='{$n->url}'" : '' }}">
                        <div class="d-flex align-items-start gap-3">
                            <div class="avatar flex-shrink-0">
                                <span class="avatar-initial rounded-circle bg-label-info">
                                    <i class="ri ri-notification-2-line"></i>
                                </span>
                            </div>
                            <div class="flex-grow-1">
                                <div class="d-flex justify-content-between">
                                    <h6 class="mb-1 {{ $n->is_read ? '' : 'fw-bold' }}">
                                        {{ $n->title }}
                                    </h6>
                                    <small class="text-body-secondary text-nowrap ms-2">
                                        {{ $n->created_at->diffForHumans() }}
                                    </small>
                                </div>
                                <p class="mb-0 text-body-secondary small">{{ $n->message }}</p>
                            </div>
                            @if (!$n->is_read)
                                <span class="badge badge-dot bg-primary mt-1"></span>
                            @endif
                        </div>
                    </li>
                @empty
                    <li class="list-group-item text-center py-5 text-muted">
                        <i class="ri ri-notification-off-line ri-48px mb-2 d-block"></i>
                        Tidak ada notifikasi
                    </li>
                @endforelse
            </ul>
        </div>
        @if ($notifikasi->hasPages())
            <div class="card-footer">
                {{ $notifikasi->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
