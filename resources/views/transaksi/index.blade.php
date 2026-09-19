@extends('layouts.app')

@section('title', 'Transaksi')
@section('page-title', 'Daftar Transaksi')

@section('content')


    {{-- FORM SUBMIT DATA --}}
    <div class="card mb-4">
        <div class="card-body">
            <div class="col-md-12 text-center">
                <button onclick="trans.bboxTransaksi(this)" type="proses" class="btn btn-success"
                    style="background:linear-gradient(135deg,#22c55e,#16a34a);">
                    <i class="bi-plus-circle me-1"></i>Masukan Data
                </button>
            </div>

        </div>
    </div>
    {{-- ─── Filter Bar ──────────────────────────────────────────── --}}

    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('transaksi') }}" class="row gy-3 gx-3 align-items-end">

                <div class="col-6 col-md-2">
                    <label class="form-label small text-muted mb-1">Bulan</label>
                    <select name="bulan" class="form-select form-select-sm">
                        @foreach (range(1, 12) as $m)
                            <option value="{{ $m }}" {{ $bulan == $m ? 'selected' : '' }}>
                                {{ \Carbon\Carbon::create()->month($m)->locale('id')->translatedFormat('F') }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-6 col-md-2">
                    <label class="form-label small text-muted mb-1">Tahun</label>
                    <select name="tahun" class="form-select form-select-sm">
                        @foreach (range(now()->year, now()->year - 2) as $y)
                            <option value="{{ $y }}" {{ $tahun == $y ? 'selected' : '' }}>{{ $y }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-12 col-md-5">
                    <label class="form-label small text-muted mb-1">Cari</label>
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
                        <input type="text" name="search" value="{{ $search }}"
                            placeholder="Keterangan, Kategori, Nomor..." class="form-control">
                    </div>
                </div>

                <div class="col-12 col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-sm text-white flex-grow-1"
                        style="background:linear-gradient(135deg,#22c55e,#16a34a);">
                        <i class="bi bi-funnel-fill me-1"></i>Filter
                    </button>
                    @if ($search || $bulan || $tahun)
                        {{-- <a href="{{ route('transaksi', ['bulan' => $bulan, 'tahun' => $tahun]) }}" --}}
                        <a href="{{ route('transaksi') }}" class="btn btn-sm btn-outline-secondary">Reset</a>
                    @endif
                </div>

            </form>
        </div>
    </div>

    {{-- ─── Tabel Transaksi ─────────────────────────────────────── --}}
    <div class="card">
        <div class="card-body">

            <div class="d-flex align-items-center justify-content-between mb-3 flex-wrap gap-2">
                <h2 class="fs-6 fw-semibold mb-0">
                    {{ $transaksi->total() }} transaksi ditemukan
                </h2>
                <span class="small text-muted">
                    Halaman {{ $transaksi->currentPage() }} dari {{ $transaksi->lastPage() }}
                </span>
            </div>

            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Nomor WA</th>
                            <th>Keterangan</th>
                            <th>Kategori</th>
                            <th class="text-end">Nominal</th>
                            <th class="text-end">Waktu</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($transaksi as $index => $t)
                            <tr>
                                <td class="text-muted small">
                                    {{ ($transaksi->currentPage() - 1) * $transaksi->perPage() + $index + 1 }}
                                </td>
                                <td class="text-muted small font-monospace">
                                    {{ substr($t->wa_number, 0, 6) }}***
                                </td>
                                <td class="fw-medium">{{ $t->keterangan }}</td>
                                <td>
                                    <span
                                        class="badge badge-soft bg-light text-dark text-capitalize border">{{ $t->kategori }}</span>
                                </td>
                                <td
                                    class="text-end fw-semibold {{ ($t->jenis_transaksi ?? '') === 'masuk' ? 'text-success' : 'text-dark' }}">
                                    Rp {{ number_format($t->nominal, 0, ',', '.') }}
                                </td>
                                <td class="text-end text-muted small">
                                    <span title="{{ $t->recorded_at->format('d M Y H:i:s') }}">
                                        {{ $t->recorded_at->format('d/m H:i') }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    <button onclick="trans.hapusTransaksi(this)" id_transaksi="<?= $t->id ?>" type="submit"
                                        class="btn btn-sm btn-outline-danger border-0" title="Hapus">
                                        <i class="bi bi-trash3"></i>
                                    </button>

                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted py-5">
                                    <i class="bi bi-inbox fs-1 d-block mb-2 text-secondary"></i>
                                    Tidak ada transaksi ditemukan
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($transaksi->hasPages())
                <div class="mt-4 pt-3 border-top d-flex justify-content-center">
                    {{ $transaksi->links() }}
                </div>
            @endif

        </div>
    </div>

@endsection


@push('scripts')
    <script src="{{ asset('assets/js/transaksi.js') }}?v={{ version_assets() }}"></script>
@endpush
