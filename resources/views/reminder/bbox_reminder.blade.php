<div class="row justify-content-center">
  <div class="col-12">

    <!-- Header -->
    <div class="text-center mb-4">
      <h4 class="fw-bold mb-1">{{ !empty($reminder) ? 'Edit Pengingat Tagihan' : 'Tambah Pengingat Tagihan Baru' }}</h4>
      <p class="text-muted mb-0">
        Jadwalkan pengingat pembayaran otomatis terintegrasi WhatsApp & Laporan Keuangan
      </p>
    </div>

    <!-- Form Body -->
    <div class="card border-0 shadow-none">
      <div class="card-body p-0">
        <input type="hidden" id="id_reminder" value="{{ $reminder->id ?? '' }}">

        <!-- Section 1: Informasi Tagihan -->
        <div class="mb-4">
          <h6 class="fw-bold mb-3 text-secondary border-bottom pb-2">Informasi Tagihan</h6>
          <div class="row g-3">

            <!-- Nama Tagihan -->
            <div class="col-12 col-md-7">
              <label for="nama_tagihan" class="form-label fw-semibold">
                Nama Tagihan <span class="text-danger">*</span>
              </label>
              <input type="text" class="form-control" id="nama_tagihan" name="nama_tagihan"
                value="{{ $reminder->nama_tagihan ?? '' }}" placeholder="Contoh: Sewa Tempat, Listrik PLN, Cicilan Motor" required>
            </div>

            <!-- Tipe Transaksi -->
            <div class="col-12 col-md-5">
              <label for="jenis_transaksi" class="form-label fw-semibold">
                Tipe Transaksi <span class="text-danger">*</span>
              </label>
              <select class="form-control form-select" id="jenis_transaksi" name="jenis_transaksi" required>
                <option value="keluar" {{ (!empty($reminder) && $reminder->jenis_transaksi === 'keluar') ? 'selected' : '' }}>
                  Pengeluaran
                </option>
                <option value="masuk" {{ (!empty($reminder) && $reminder->jenis_transaksi === 'masuk') ? 'selected' : '' }}>
                  Pemasukan / Piutang
                </option>
              </select>
            </div>

            <!-- Total Nominal -->
            <div class="col-12 col-md-6">
              <label for="total_nominal" class="form-label fw-semibold">
                Total Nominal <span class="text-danger">*</span>
              </label>
              <div class="input-group">
                <span class="input-group-text fw-semibold">Rp</span>
                <input type="text" class="form-control" id="total_nominal" name="total_nominal"
                  value="{{ !empty($reminder) ? number_format($reminder->total_nominal, 0, ',', ',') : '' }}"
                  placeholder="0" required oninput="reminder.numberFormat(this)">
              </div>
              <div class="form-text">Masukkan nominal keseluruhan tagihan.</div>
            </div>

            <!-- Tanggal Jatuh Tempo -->
            <div class="col-12 col-md-6">
              <label for="tanggal_jatuh_tempo" class="form-label fw-semibold">
                Tanggal Jatuh Tempo <span class="text-danger">*</span>
              </label>
              <input type="text" class="form-control" id="tanggal_jatuh_tempo" name="tanggal_jatuh_tempo"
                value="{{ !empty($reminder) ? $reminder->tanggal_jatuh_tempo->format('Y-m-d') : '' }}"
                placeholder="YYYY-MM-DD" required>
              <div class="form-text">Notifikasi WhatsApp dikirim mulai H-3 s.d H-1.</div>
            </div>

          </div>
        </div>

        <!-- Section 2: Konfigurasi Reminder & Notifikasi -->
        <div class="mb-4">
          <h6 class="fw-bold mb-3 text-secondary border-bottom pb-2">Kontak & Kategori</h6>
          <div class="row g-3">

            <!-- Nomor WhatsApp Tujuan -->
            <div class="col-12 col-md-6">
              <label for="wa_number" class="form-label fw-semibold">
                Nomor WhatsApp Tujuan <span class="text-danger">*</span>
              </label>
              <div class="input-group">
                <span class="input-group-text"><i class="bi bi-whatsapp text-success"></i></span>
                <input type="text" class="form-control" id="wa_number" name="wa_number"
                  value="{{ $reminder->wa_number ?? $defaultWaNumber }}" placeholder="08xxxxxxxxxx atau 628xxxxxxxxxx">
              </div>
              <div class="form-text">Nomor WA yang akan menerima pesan pengingat proaktif.</div>
            </div>

            <!-- Kategori -->
            <div class="col-12 col-md-6">
              <label for="kategori" class="form-label fw-semibold">
                Kategori <span class="text-danger">*</span>
              </label>
              <select class="form-control" id="kategori" name="kategori" required>
                <option value="" selected disabled>-- Pilih Kategori --</option>
                @foreach ($kategori as $vk)
                  <option value="{{ $vk['nama_kategori'] }}"
                    {{ (!empty($reminder) && $reminder->kategori === $vk['nama_kategori']) ? 'selected' : '' }}>
                    {{ $vk['nama_kategori'] . ' (' . $vk['jenis'] . ')' }}
                  </option>
                @endforeach
              </select>
            </div>

            <!-- Perulangan (Recurring) -->
            <div class="col-12 col-md-6">
              <label for="perulangan" class="form-label fw-semibold">
                Perulangan (Recurring)
              </label>
              <select class="form-control form-select" id="perulangan" name="perulangan">
                <option value="tidak" {{ (!empty($reminder) && $reminder->perulangan === 'tidak') ? 'selected' : '' }}>Tidak Berulang (Sekali Bayar)</option>
                <option value="mingguan" {{ (!empty($reminder) && $reminder->perulangan === 'mingguan') ? 'selected' : '' }}>Mingguan</option>
                <option value="bulanan" {{ (!empty($reminder) && $reminder->perulangan === 'bulanan') ? 'selected' : '' }}>Bulanan</option>
                <option value="tahunan" {{ (!empty($reminder) && $reminder->perulangan === 'tahunan') ? 'selected' : '' }}>Tahunan</option>
              </select>
            </div>

            <!-- Keterangan / Catatan -->
            <div class="col-12 col-md-6">
              <label for="keterangan" class="form-label fw-semibold">
                Catatan / Keterangan
              </label>
              <textarea class="form-control" id="keterangan" name="keterangan" rows="2"
                placeholder="Contoh: No Rekening BCA 123456 a/n Ryan">{{ $reminder->keterangan ?? '' }}</textarea>
            </div>

          </div>
        </div>

        <hr class="my-4">

        <!-- Actions -->
        <div class="d-flex justify-content-end gap-2">
          <button type="button" class="btn btn-light border px-4" data-bs-dismiss="modal">
            Batal
          </button>
          <button type="button" class="btn btn-primary px-4" onclick="reminder.submitReminder(this)">
            {{ !empty($reminder) ? 'Perbarui Tagihan' : 'Simpan Tagihan' }}
          </button>
        </div>

      </div>
    </div>

  </div>
</div>
