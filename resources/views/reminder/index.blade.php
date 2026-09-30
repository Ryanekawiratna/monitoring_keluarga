@extends('layouts.app')

@section('title', 'Reminder Tagihan')
@section('page-title', 'Reminder Tagihan & Pembayaran')

@section('content')

    {{-- ─── Stat Cards ───────────────────────────────────────────── --}}
    <div class="row g-3 mb-4">
        <div class="col-12 col-sm-6 col-xl-4">
            <div class="stat-card">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="stat-label">Total Sisa Tagihan Aktif</span>
                    <div class="stat-icon bg-warning bg-opacity-10 text-warning">
                        <i class="bi bi-wallet2 fs-5"></i>
                    </div>
                </div>
                <div class="stat-value text-dark">
                    Rp {{ number_format($totalSisaTagihanAktif, 0, ',', '.') }}
                </div>
                <div class="small text-muted mt-1">
                    Tagihan yang belum lunas
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-4">
            <div class="stat-card">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="stat-label">Jatuh Tempo Segera</span>
                    <div class="stat-icon bg-primary bg-opacity-10 text-primary">
                        <i class="bi bi-clock-history fs-5"></i>
                    </div>
                </div>
                <div class="stat-value text-primary">
                    {{ $jumlahTagihanSegera }} <span class="fs-6 text-muted fw-normal">Tagihan</span>
                </div>
                <div class="small text-muted mt-1">
                    Jatuh tempo dalam 7 hari ke depan
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-4">
            <div class="stat-card">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="stat-label">Tagihan Overdue (Telat)</span>
                    <div class="stat-icon bg-danger bg-opacity-10 text-danger">
                        <i class="bi bi-exclamation-triangle-fill fs-5"></i>
                    </div>
                </div>
                <div class="stat-value text-danger">
                    {{ $jumlahTagihanOverdue }} <span class="fs-6 text-muted fw-normal">Tagihan</span>
                </div>
                <div class="small text-muted mt-1">
                    Melewati tanggal jatuh tempo
                </div>
            </div>
        </div>
    </div>

    {{-- ─── Action Button & Filter Bar ──────────────────────────── --}}
    <div class="card mb-4">
        <div class="card-body">
            <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
                <div>
                    <button onclick="reminder.bboxReminder(this)" type="button" class="btn btn-success text-white px-3"
                        style="background:linear-gradient(135deg,#22c55e,#16a34a);">
                        <i class="bi bi-plus-circle me-1"></i> Buat Tagihan Baru
                    </button>
                </div>

                <form method="GET" action="{{ route('reminder') }}" class="row gy-2 gx-2 align-items-center">
                    <div class="col-auto">
                        <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                            <option value="aktif" {{ $status == 'aktif' ? 'selected' : '' }}>Aktif & Telat</option>
                            <option value="lunas" {{ $status == 'lunas' ? 'selected' : '' }}>Sudah Lunas</option>
                            <option value="overdue" {{ $status == 'overdue' ? 'selected' : '' }}>Khusus Overdue</option>
                            <option value="nonaktif" {{ $status == 'nonaktif' ? 'selected' : '' }}>Nonaktif / Jeda</option>
                            <option value="semua" {{ $status == 'semua' ? 'selected' : '' }}>Semua Status</option>
                        </select>
                    </div>

                    <div class="col-auto">
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
                            <input type="text" name="search" value="{{ $search }}"
                                placeholder="Nama Tagihan, Kategori..." class="form-control">
                        </div>
                    </div>

                    <div class="col-auto">
                        <button type="submit" class="btn btn-sm btn-outline-success">
                            Cari
                        </button>
                        @if ($search || $status !== 'aktif')
                            <a href="{{ route('reminder') }}" class="btn btn-sm btn-outline-secondary">Reset</a>
                        @endif
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- ─── Tabel Daftar Reminder Tagihan ────────────────────────── --}}
    <div class="card">
        <div class="card-body">

            <div class="d-flex align-items-center justify-content-between mb-3 flex-wrap gap-2">
                <h2 class="fs-6 fw-semibold mb-0">
                    {{ $reminders->total() }} Data Tagihan Terdaftar
                </h2>
                <span class="small text-muted">
                    Halaman {{ $reminders->currentPage() }} dari {{ $reminders->lastPage() }}
                </span>
            </div>

            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Nama Tagihan</th>
                            <th>Tipe & Kategori</th>
                            <th class="text-end">Sisa Tagihan</th>
                            <th class="text-end">Total Tagihan</th>
                            <th>Jatuh Tempo</th>
                            <th class="text-center">Status</th>
                            <th class="text-center" style="min-width: 170px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($reminders as $index => $r)
                            @php
                                $isOverdue = $r->status === 'overdue' || ($r->status === 'aktif' && $r->tanggal_jatuh_tempo->isPast() && !$r->tanggal_jatuh_tempo->isToday());
                            @endphp
                            <tr class="{{ $isOverdue ? 'table-danger table-opacity-10' : '' }}">
                                <td class="text-muted small">
                                    {{ ($reminders->currentPage() - 1) * $reminders->perPage() + $index + 1 }}
                                </td>
                                <td>
                                    <div class="fw-semibold text-dark">{{ $r->nama_tagihan }}</div>
                                    <div class="small text-muted font-monospace">
                                        <i class="bi bi-whatsapp text-success me-1"></i>{{ substr($r->wa_number, 0, 6) }}***
                                    </div>
                                    @if ($r->keterangan)
                                        <div class="small text-muted fst-italic">{{ Str::limit($r->keterangan, 40) }}</div>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge {{ $r->jenis_transaksi === 'masuk' ? 'bg-success' : 'bg-secondary' }} text-capitalize">
                                        {{ $r->jenis_transaksi === 'masuk' ? 'Pemasukan' : 'Pengeluaran' }}
                                    </span>
                                    <div class="small text-muted mt-1">
                                        <i class="bi bi-tag me-1"></i>{{ $r->kategori }}
                                    </div>
                                </td>
                                <td class="text-end fw-bold {{ $r->sisa_tagihan > 0 ? 'text-danger' : 'text-success' }}">
                                    Rp {{ number_format($r->sisa_tagihan, 0, ',', '.') }}
                                    @if ($r->payments->count() > 0)
                                        <div class="small fw-normal text-muted">
                                            ({{ $r->payments->count() }}x cicilan)
                                        </div>
                                    @endif
                                </td>
                                <td class="text-end fw-semibold text-muted">
                                    Rp {{ number_format($r->total_nominal, 0, ',', '.') }}
                                </td>
                                <td>
                                    <div class="fw-medium {{ $isOverdue ? 'text-danger fw-bold' : '' }}">
                                        {{ $r->tanggal_jatuh_tempo->locale('id')->translatedFormat('d M Y') }}
                                    </div>
                                    <div class="small text-muted">
                                        Perulangan: <span class="text-capitalize">{{ $r->perulangan }}</span>
                                    </div>
                                </td>
                                <td class="text-center">
                                    @if ($r->status === 'lunas')
                                        <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1">
                                            <i class="bi bi-check-circle-fill me-1"></i>Lunas
                                        </span>
                                    @elseif ($r->status === 'nonaktif')
                                        <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle px-2 py-1">
                                            <i class="bi bi-pause-circle me-1"></i>Dijeda
                                        </span>
                                    @elseif ($isOverdue)
                                        <span class="badge bg-danger text-white px-2 py-1">
                                            <i class="bi bi-exclamation-octagon-fill me-1"></i>Overdue
                                        </span>
                                    @else
                                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1">
                                            <i class="bi bi-hourglass-split me-1"></i>Aktif
                                        </span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <div class="btn-action-group">
                                        @if ($r->status !== 'lunas')
                                            {{-- Tombol Bayar / Cicil --}}
                                            <button onclick="reminder.bboxBayar(this, {{ $r->id }})"
                                                class="btn-action-bayar" title="Bayar / Cicil Tagihan">
                                                <i class="bi bi-wallet2"></i>
                                                <span>Bayar</span>
                                            </button>
                                        @endif

                                        {{-- Tombol Edit --}}
                                        <button onclick="reminder.bboxReminder(this, {{ $r->id }})"
                                            class="btn-action-icon btn-action-edit" title="Edit Tagihan">
                                            <i class="bi bi-pencil-fill"></i>
                                        </button>

                                        {{-- Tombol Pause / Nonaktifkan --}}
                                        <button onclick="reminder.toggleStatus(this, {{ $r->id }})"
                                            class="btn-action-icon {{ $r->status === 'nonaktif' ? 'btn-action-resume' : 'btn-action-pause' }}"
                                            title="{{ $r->status === 'nonaktif' ? 'Aktifkan Reminder' : 'Jeda Reminder' }}">
                                            <i class="bi {{ $r->status === 'nonaktif' ? 'bi-play-circle-fill' : 'bi-pause-circle-fill' }}"></i>
                                        </button>

                                        {{-- Tombol Hapus --}}
                                        <button onclick="reminder.hapusReminder(this, {{ $r->id }})"
                                            class="btn-action-icon btn-action-delete" title="Hapus Tagihan">
                                            <i class="bi bi-trash3-fill"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted py-5">
                                    <i class="bi bi-bell-slash fs-1 d-block mb-2 text-secondary"></i>
                                    Belum ada tagihan terdaftar
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($reminders->hasPages())
                <div class="mt-4 pt-3 border-top d-flex justify-content-center">
                    {{ $reminders->links() }}
                </div>
            @endif

        </div>
    </div>

@endsection

@push('styles')
    <style>
        /* ── Reminder Action Buttons ────────────────────────────── */
        .btn-action-group {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            flex-wrap: nowrap;
        }

        .btn-action-bayar {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            height: 32px;
            padding: 0 12px;
            font-size: 0.8rem;
            font-weight: 600;
            color: #ffffff !important;
            background: linear-gradient(135deg, #16a34a, #15803d);
            border: none;
            border-radius: 8px;
            white-space: nowrap;
            box-shadow: 0 2px 4px rgba(22, 163, 74, 0.25);
            transition: all 0.15s ease;
            text-decoration: none;
            cursor: pointer;
        }

        .btn-action-bayar:hover {
            background: linear-gradient(135deg, #15803d, #14532d);
            transform: translateY(-1px);
            box-shadow: 0 4px 8px rgba(22, 163, 74, 0.35);
        }

        .btn-action-bayar:active {
            transform: translateY(0);
        }

        .btn-action-bayar i {
            font-size: 0.95rem;
        }

        .btn-action-icon {
            width: 32px;
            height: 32px;
            min-width: 32px;
            min-height: 32px;
            padding: 0;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 8px;
            font-size: 0.92rem;
            transition: all 0.15s ease;
            border: 1px solid transparent;
            cursor: pointer;
            text-decoration: none;
        }

        .btn-action-icon:hover {
            transform: translateY(-1px);
        }

        .btn-action-icon:active {
            transform: translateY(0);
        }

        /* Edit: Soft Blue */
        .btn-action-edit {
            background: #eff6ff;
            color: #2563eb;
            border-color: #bfdbfe;
        }

        .btn-action-edit:hover {
            background: #2563eb;
            color: #ffffff;
            border-color: #2563eb;
            box-shadow: 0 3px 6px rgba(37, 99, 235, 0.25);
        }

        /* Pause: Soft Amber */
        .btn-action-pause {
            background: #fffbeb;
            color: #d97706;
            border-color: #fde68a;
        }

        .btn-action-pause:hover {
            background: #d97706;
            color: #ffffff;
            border-color: #d97706;
            box-shadow: 0 3px 6px rgba(217, 119, 6, 0.25);
        }

        /* Resume: Soft Emerald */
        .btn-action-resume {
            background: #ecfdf5;
            color: #059669;
            border-color: #a7f3d0;
        }

        .btn-action-resume:hover {
            background: #059669;
            color: #ffffff;
            border-color: #059669;
            box-shadow: 0 3px 6px rgba(5, 150, 105, 0.25);
        }

        /* Delete: Soft Red */
        .btn-action-delete {
            background: #fef2f2;
            color: #dc2626;
            border-color: #fecaca;
        }

        .btn-action-delete:hover {
            background: #dc2626;
            color: #ffffff;
            border-color: #dc2626;
            box-shadow: 0 3px 6px rgba(220, 38, 38, 0.25);
        }
    </style>
@endpush

@push('scripts')
    <script src="{{ asset('assets/js/reminder.js') }}?v={{ version_assets() }}"></script>
@endpush
