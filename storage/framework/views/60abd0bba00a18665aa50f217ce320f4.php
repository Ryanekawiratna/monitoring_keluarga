<?php $__env->startSection('title', 'Dashboard'); ?>
<?php $__env->startSection('page-title', 'Dashboard'); ?>

<?php $__env->startSection('content'); ?>

    
    <div class="row g-3 mb-4">

        <div class="col-6 col-xl-3">
            <div class="stat-card h-100">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <span class="stat-label">Total Hari Ini</span>
                    <div class="stat-icon" style="background:#eff6ff; color:#3b82f6;">
                        <i class="bi bi-calendar-day"></i>
                    </div>
                </div>
                <p class="stat-value mb-0">Rp <?php echo e(number_format($totalHariIni, 0, ',', '.')); ?></p>
                <p class="stat-sub mb-0"><?php echo e(now()->locale('id')->translatedFormat('d F Y')); ?></p>
            </div>
        </div>

        <div class="col-6 col-xl-3">
            <div class="stat-card h-100">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <span class="stat-label">Pengeluaran Bulan Ini</span>
                    <div class="stat-icon" style="background:#fef2f2; color:#ef4444;">
                        <i class="bi bi-graph-down-arrow"></i>
                    </div>
                </div>
                <p class="stat-value mb-0">Rp <?php echo e(number_format($totalBulanIni, 0, ',', '.')); ?></p>
                <p class="stat-sub mb-0"><?php echo e(now()->locale('id')->translatedFormat('F Y')); ?></p>
            </div>
        </div>

        <div class="col-6 col-xl-3">
            <div class="stat-card h-100">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <span class="stat-label">Pemasukan Bulan Ini</span>
                    <div class="stat-icon" style="background:#f0fdf4; color:#16a34a;">
                        <i class="bi bi-graph-up-arrow"></i>
                    </div>
                </div>
                <p class="stat-value mb-0">Rp <?php echo e(number_format($totalMasukBulanIni, 0, ',', '.')); ?></p>
                <p class="stat-sub mb-0"><?php echo e(now()->locale('id')->translatedFormat('F Y')); ?></p>
            </div>
        </div>

        <div class="col-6 col-xl-3">
            <div class="stat-card h-100">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <span class="stat-label">Transaksi Bulan Ini</span>
                    <div class="stat-icon" style="background:#faf5ff; color:#a855f7;">
                        <i class="bi bi-receipt"></i>
                    </div>
                </div>
                <p class="stat-value mb-0"><?php echo e(number_format($totalTransaksi)); ?></p>
                <p class="stat-sub mb-0">Entri tercatat</p>
            </div>
        </div>

        <div class="col-6 col-xl-3">
            <div class="stat-card h-100">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <span class="stat-label">Pengguna Aktif</span>
                    <div class="stat-icon" style="background:#fff7ed; color:#f97316;">
                        <i class="bi bi-people-fill"></i>
                    </div>
                </div>
                <p class="stat-value mb-0"><?php echo e($penggunnaAktif); ?></p>
                <p class="stat-sub mb-0">Nomor unik bulan ini</p>
            </div>
        </div>

    </div>

    
    <div class="row g-4 mb-4">

        <div class="col-xl-8">
            <div class="card h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <h2 class="fs-6 fw-semibold mb-0">Tren Transaksi 30 Hari Terakhir</h2>
                    </div>
                    <canvas id="trenChart" height="110"></canvas>
                </div>
            </div>
        </div>

        <div class="col-xl-4">
            <div class="card h-100">
                <div class="card-body">
                    <h2 class="fs-6 fw-semibold mb-3">Per Kategori Bulan Ini</h2>
                    <?php if($perKategori->isEmpty()): ?>
                        <p class="text-muted small text-center py-5 mb-0">Belum ada data</p>
                    <?php else: ?>
                        <?php $totalBulan = $perKategori->sum('total'); ?>
                        <?php $__currentLoopData = $perKategori; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php $pct = $totalBulan > 0 ? round($item->total / $totalBulan * 100) : 0; ?>
                            <div class="mb-3">
                                <div class="d-flex align-items-center justify-content-between mb-1">
                                    <span class="small fw-medium text-capitalize"><?php echo e($item->kategori); ?></span>
                                    <span class="small text-muted"><?php echo e($pct); ?>%</span>
                                </div>
                                <div class="progress">
                                    <div class="progress-bar progress-bar-brand" style="width: <?php echo e($pct); ?>%"></div>
                                </div>
                                <p class="text-muted mb-0" style="font-size:.72rem;">
                                    Rp <?php echo e(number_format($item->total, 0, ',', '.')); ?> · <?php echo e($item->jumlah); ?>x
                                </p>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>

    </div>

    
    <div class="row g-4">

        <div class="col-xl-8">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <h2 class="fs-6 fw-semibold mb-0">Transaksi Terbaru</h2>
                        <a href="<?php echo e(route('transaksi')); ?>" class="small fw-medium" style="color:var(--brand-600);">
                            Lihat semua <i class="bi bi-arrow-right"></i>
                        </a>
                    </div>

                    <ul class="nav nav-pills mb-3 gap-2" id="jenisTab" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" data-bs-toggle="pill" data-bs-target="#tab-semua"
                                type="button">Semua</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" data-bs-toggle="pill" data-bs-target="#tab-masuk"
                                type="button">Pemasukan</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" data-bs-toggle="pill" data-bs-target="#tab-keluar"
                                type="button">Pengeluaran</button>
                        </li>
                    </ul>

                    <div class="tab-content">
                        <?php $__currentLoopData = ['semua' => null, 'masuk' => 'masuk', 'keluar' => 'keluar']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $filterJenis): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div class="tab-pane fade <?php echo e($key === 'semua' ? 'show active' : ''); ?>"
                                id="tab-<?php echo e($key); ?>">
                                <div class="table-responsive">
                                    <table class="table align-middle mb-0">
                                        <thead>
                                            <tr>
                                                <th>Keterangan</th>
                                                <th>Kategori</th>
                                                <th class="text-end">Nominal</th>
                                                <th class="text-end">Waktu</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                                $rows = $filterJenis
                                                    ? $transaksiTerbaru->where('jenis_transaksi', $filterJenis)
                                                    : $transaksiTerbaru;
                                            ?>
                                            <?php $__empty_1 = true; $__currentLoopData = $rows; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $t): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                                <tr>
                                                    <td class="fw-medium"><?php echo e($t->keterangan); ?></td>
                                                    <td><span
                                                            class="badge badge-soft bg-light text-dark text-capitalize border"><?php echo e($t->kategori); ?></span>
                                                    </td>
                                                    <td
                                                        class="text-end fw-semibold <?php echo e($t->jenis_transaksi === 'masuk' ? 'text-success' : 'text-dark'); ?>">
                                                        <?php echo e($t->jenis_transaksi === 'masuk' ? '+' : '-'); ?>Rp
                                                        <?php echo e(number_format($t->nominal, 0, ',', '.')); ?>

                                                    </td>
                                                    <td class="text-end text-muted small">
                                                        <?php echo e($t->recorded_at->diffForHumans()); ?></td>
                                                </tr>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                                <tr>
                                                    <td colspan="4" class="text-center text-muted py-4">Belum ada
                                                        transaksi</td>
                                                </tr>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>

                </div>
            </div>
        </div>

        
        <div class="col-xl-4">
            <div class="card h-100">
                <div class="card-body">
                    <h2 class="fs-6 fw-semibold mb-3">Top Pengguna Bulan Ini</h2>
                    <?php if($topPengguna->isEmpty()): ?>
                        <p class="text-muted small text-center py-5 mb-0">Belum ada data</p>
                    <?php else: ?>
                        <?php $__currentLoopData = $topPengguna; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $user): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div class="d-flex align-items-center gap-3 <?php echo e(!$loop->last ? 'mb-3' : ''); ?>">
                                <div class="rounded-circle d-flex align-items-center justify-content-center fw-bold flex-shrink-0"
                                    style="width:32px;height:32px;font-size:.75rem;
                                     <?php echo e($index === 0 ? 'background:#fef9c3;color:#a16207;' : 'background:#f1f5f9;color:#475569;'); ?>">
                                    <?php echo e($index + 1); ?>

                                </div>
                                <div class="flex-grow-1 min-w-0">
                                    <p class="mb-0 small fw-medium text-truncate"><?php echo e(substr($user->wa_number, 0, 6)); ?>***
                                    </p>
                                    <p class="mb-0 text-muted" style="font-size:.72rem;"><?php echo e($user->jumlah); ?>x · Rp
                                        <?php echo e(number_format($user->total, 0, ',', '.')); ?></p>
                                </div>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>

    </div>

<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
    <script>
        const trenCtx = document.getElementById('trenChart');
        new Chart(trenCtx, {
            type: 'line',
            data: {
                labels: <?php echo json_encode($labels, 15, 512) ?>,
                datasets: [{
                    label: 'Total Transaksi',
                    data: <?php echo json_encode($values, 15, 512) ?>,
                    borderColor: '#16a34a',
                    backgroundColor: 'rgba(34,197,94,.12)',
                    tension: .35,
                    fill: true,
                    pointRadius: 0,
                    borderWidth: 2,
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        ticks: {
                            callback: (v) => 'Rp ' + new Intl.NumberFormat('id-ID').format(v)
                        },
                        grid: {
                            color: '#f1f5f9'
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        }
                    }
                }
            }
        });
    </script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\user\Downloads\rumanesia\resources\views/dashboard/index.blade.php ENDPATH**/ ?>