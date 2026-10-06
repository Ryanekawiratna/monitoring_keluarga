<?php $__env->startSection('title', 'Reminder Tagihan'); ?>
<?php $__env->startSection('page-title', 'Reminder Tagihan & Pembayaran'); ?>

<?php $__env->startSection('content'); ?>

    
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
                    Rp <?php echo e(number_format($totalSisaTagihanAktif, 0, ',', '.')); ?>

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
                    <?php echo e($jumlahTagihanSegera); ?> <span class="fs-6 text-muted fw-normal">Tagihan</span>
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
                    <?php echo e($jumlahTagihanOverdue); ?> <span class="fs-6 text-muted fw-normal">Tagihan</span>
                </div>
                <div class="small text-muted mt-1">
                    Melewati tanggal jatuh tempo
                </div>
            </div>
        </div>
    </div>

    
    <div class="card mb-4">
        <div class="card-body">
            <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
                <div>
                    <button onclick="reminder.bboxReminder(this)" type="button" class="btn btn-success text-white px-3"
                        style="background:linear-gradient(135deg,#22c55e,#16a34a);">
                        <i class="bi bi-plus-circle me-1"></i> Buat Tagihan Baru
                    </button>
                </div>

                <form method="GET" action="<?php echo e(route('reminder')); ?>" class="row gy-2 gx-2 align-items-center">
                    <div class="col-auto">
                        <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                            <option value="aktif" <?php echo e($status == 'aktif' ? 'selected' : ''); ?>>Aktif & Telat</option>
                            <option value="lunas" <?php echo e($status == 'lunas' ? 'selected' : ''); ?>>Sudah Lunas</option>
                            <option value="overdue" <?php echo e($status == 'overdue' ? 'selected' : ''); ?>>Khusus Overdue</option>
                            <option value="nonaktif" <?php echo e($status == 'nonaktif' ? 'selected' : ''); ?>>Nonaktif / Jeda</option>
                            <option value="semua" <?php echo e($status == 'semua' ? 'selected' : ''); ?>>Semua Status</option>
                        </select>
                    </div>

                    <div class="col-auto">
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
                            <input type="text" name="search" value="<?php echo e($search); ?>"
                                placeholder="Nama Tagihan, Kategori..." class="form-control">
                        </div>
                    </div>

                    <div class="col-auto">
                        <button type="submit" class="btn btn-sm btn-outline-success">
                            Cari
                        </button>
                        <?php if($search || $status !== 'aktif'): ?>
                            <a href="<?php echo e(route('reminder')); ?>" class="btn btn-sm btn-outline-secondary">Reset</a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
        </div>
    </div>

    
    <div class="card">
        <div class="card-body">

            <div class="d-flex align-items-center justify-content-between mb-3 flex-wrap gap-2">
                <h2 class="fs-6 fw-semibold mb-0">
                    <?php echo e($reminders->total()); ?> Data Tagihan Terdaftar
                </h2>
                <span class="small text-muted">
                    Halaman <?php echo e($reminders->currentPage()); ?> dari <?php echo e($reminders->lastPage()); ?>

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
                        <?php $__empty_1 = true; $__currentLoopData = $reminders; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $r): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <?php
                                $isOverdue = $r->status === 'overdue' || ($r->status === 'aktif' && $r->tanggal_jatuh_tempo->isPast() && !$r->tanggal_jatuh_tempo->isToday());
                            ?>
                            <tr class="<?php echo e($isOverdue ? 'table-danger table-opacity-10' : ''); ?>">
                                <td class="text-muted small">
                                    <?php echo e(($reminders->currentPage() - 1) * $reminders->perPage() + $index + 1); ?>

                                </td>
                                <td>
                                    <div class="fw-semibold text-dark"><?php echo e($r->nama_tagihan); ?></div>
                                    <div class="small text-muted font-monospace">
                                        <i class="bi bi-whatsapp text-success me-1"></i><?php echo e(substr($r->wa_number, 0, 6)); ?>***
                                    </div>
                                    <?php if($r->keterangan): ?>
                                        <div class="small text-muted fst-italic"><?php echo e(Str::limit($r->keterangan, 40)); ?></div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge <?php echo e($r->jenis_transaksi === 'masuk' ? 'bg-success' : 'bg-secondary'); ?> text-capitalize">
                                        <?php echo e($r->jenis_transaksi === 'masuk' ? 'Pemasukan' : 'Pengeluaran'); ?>

                                    </span>
                                    <div class="small text-muted mt-1">
                                        <i class="bi bi-tag me-1"></i><?php echo e($r->kategori); ?>

                                    </div>
                                </td>
                                <td class="text-end fw-bold <?php echo e($r->sisa_tagihan > 0 ? 'text-danger' : 'text-success'); ?>">
                                    Rp <?php echo e(number_format($r->sisa_tagihan, 0, ',', '.')); ?>

                                    <?php if($r->payments->count() > 0): ?>
                                        <div class="small fw-normal text-muted">
                                            (<?php echo e($r->payments->count()); ?>x cicilan)
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end fw-semibold text-muted">
                                    Rp <?php echo e(number_format($r->total_nominal, 0, ',', '.')); ?>

                                </td>
                                <td>
                                    <div class="fw-medium <?php echo e($isOverdue ? 'text-danger fw-bold' : ''); ?>">
                                        <?php echo e($r->tanggal_jatuh_tempo->locale('id')->translatedFormat('d M Y')); ?>

                                    </div>
                                    <div class="small text-muted">
                                        Perulangan: <span class="text-capitalize"><?php echo e($r->perulangan); ?></span>
                                    </div>
                                </td>
                                <td class="text-center">
                                    <?php if($r->status === 'lunas'): ?>
                                        <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1">
                                            <i class="bi bi-check-circle-fill me-1"></i>Lunas
                                        </span>
                                    <?php elseif($r->status === 'nonaktif'): ?>
                                        <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle px-2 py-1">
                                            <i class="bi bi-pause-circle me-1"></i>Dijeda
                                        </span>
                                    <?php elseif($isOverdue): ?>
                                        <span class="badge bg-danger text-white px-2 py-1">
                                            <i class="bi bi-exclamation-octagon-fill me-1"></i>Overdue
                                        </span>
                                    <?php else: ?>
                                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1">
                                            <i class="bi bi-hourglass-split me-1"></i>Aktif
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <div class="btn-action-group">
                                        <?php if($r->status !== 'lunas'): ?>
                                            
                                            <button onclick="reminder.bboxBayar(this, <?php echo e($r->id); ?>)"
                                                class="btn-action-bayar" title="Bayar / Cicil Tagihan">
                                                <i class="bi bi-wallet2"></i>
                                                <span>Bayar</span>
                                            </button>
                                        <?php endif; ?>

                                        
                                        <button onclick="reminder.bboxReminder(this, <?php echo e($r->id); ?>)"
                                            class="btn-action-icon btn-action-edit" title="Edit Tagihan">
                                            <i class="bi bi-pencil-fill"></i>
                                        </button>

                                        
                                        <button onclick="reminder.toggleStatus(this, <?php echo e($r->id); ?>)"
                                            class="btn-action-icon <?php echo e($r->status === 'nonaktif' ? 'btn-action-resume' : 'btn-action-pause'); ?>"
                                            title="<?php echo e($r->status === 'nonaktif' ? 'Aktifkan Reminder' : 'Jeda Reminder'); ?>">
                                            <i class="bi <?php echo e($r->status === 'nonaktif' ? 'bi-play-circle-fill' : 'bi-pause-circle-fill'); ?>"></i>
                                        </button>

                                        
                                        <button onclick="reminder.hapusReminder(this, <?php echo e($r->id); ?>)"
                                            class="btn-action-icon btn-action-delete" title="Hapus Tagihan">
                                            <i class="bi bi-trash3-fill"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="8" class="text-center text-muted py-5">
                                    <i class="bi bi-bell-slash fs-1 d-block mb-2 text-secondary"></i>
                                    Belum ada tagihan terdaftar
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <?php if($reminders->hasPages()): ?>
                <div class="mt-4 pt-3 border-top d-flex justify-content-center">
                    <?php echo e($reminders->links()); ?>

                </div>
            <?php endif; ?>

        </div>
    </div>

<?php $__env->stopSection(); ?>

<?php $__env->startPush('styles'); ?>
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
<?php $__env->stopPush(); ?>

<?php $__env->startPush('scripts'); ?>
    <script src="<?php echo e(asset('assets/js/reminder.js')); ?>?v=<?php echo e(version_assets()); ?>"></script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\user\Downloads\rumanesia\resources\views/reminder/index.blade.php ENDPATH**/ ?>