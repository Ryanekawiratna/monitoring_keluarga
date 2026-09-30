@php
  $terbayar = max(0, $reminder->total_nominal - $reminder->sisa_tagihan);
  $isOverdue = $reminder->tanggal_jatuh_tempo && $reminder->tanggal_jatuh_tempo->isPast() && !$reminder->tanggal_jatuh_tempo->isToday();

  $defaultNextDue = $reminder->tanggal_jatuh_tempo
      ? $reminder->tanggal_jatuh_tempo->format('Y-m-d')
      : now()->format('Y-m-d');
@endphp

<style>
  .bbox-bayar-wrap {
    font-family: inherit;
    color: #1e293b;
  }

  /* Hero Card */
  .sisa-hero {
    background: #f8fafb;
    border: 1px solid #e5e7eb;
    border-radius: 14px;
    padding: 1.25rem 1.35rem;
  }

  /* Radio-style mode cards */
  .mode-card {
    cursor: pointer;
    background: #fff;
    border: 2px solid #e5e7eb;
    border-radius: 12px;
    padding: 0.85rem 1rem;
    transition: all .18s ease;
    user-select: none;
    position: relative;
  }
  .mode-card:hover {
    border-color: #d1d5db;
    background: #fafbfc;
  }
  .mode-card.active {
    border-color: var(--brand-600, #16a34a);
    background: #f0fdf4;
  }

  /* Radio circle indicator */
  .radio-circle {
    width: 20px;
    height: 20px;
    border-radius: 50%;
    border: 2px solid #cbd5e1;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    transition: all .15s ease;
  }
  .radio-circle .inner {
    width: 10px;
    height: 10px;
    border-radius: 50%;
    background: transparent;
    transition: all .15s ease;
  }
  .mode-card.active .radio-circle {
    border-color: var(--brand-600, #16a34a);
  }
  .mode-card.active .radio-circle .inner {
    background: var(--brand-600, #16a34a);
  }

  /* Small inline input inside cicil card */
  .cicil-inline-input {
    width: 100%;
    border: 1px solid #d1d5db;
    border-radius: 8px;
    padding: 0.3rem 0.5rem;
    font-size: 0.85rem;
    font-weight: 600;
    color: #1e293b;
    text-align: right;
    outline: none;
    transition: border-color .15s ease;
  }
  .cicil-inline-input:focus {
    border-color: var(--brand-600, #16a34a);
    box-shadow: 0 0 0 2px rgba(22,163,74,.12);
  }
  .cicil-inline-input::placeholder {
    color: #94a3b8;
    font-weight: 400;
  }

  /* Chip tag buttons */
  .chip-tag-btn {
    cursor: pointer;
    font-size: 0.73rem;
    font-weight: 500;
    padding: 0.22rem 0.5rem;
    border-radius: 6px;
    background: #f1f5f9;
    border: 1px solid #e2e8f0;
    color: #475569;
    transition: all 0.12s ease;
  }
  .chip-tag-btn:hover {
    background: #e2e8f0;
    color: #0f172a;
    border-color: #cbd5e1;
  }

  .text-xs { font-size: 0.75rem; }
  .rotate-180 { transform: rotate(180deg); }
  .transition-all { transition: all 0.2s ease; }
</style>

<div class="bbox-bayar-wrap">

  {{-- ── Header ────────────────────────────────────────────────── --}}
  <div class="d-flex align-items-start justify-content-between pb-3 mb-3 border-bottom">
    <div class="d-flex align-items-center gap-2">
      <div class="rounded-3 d-flex align-items-center justify-content-center"
           style="width:40px;height:40px;background:linear-gradient(135deg,#dcfce7,#bbf7d0);color:#15803d;">
        <i class="bi bi-wallet2 fs-5"></i>
      </div>
      <div>
        <h5 class="fw-bold mb-0" style="font-size:1.1rem;">Pembayaran Tagihan</h5>
        <div class="d-flex align-items-center gap-1 flex-wrap mt-1">
          <span class="fw-semibold text-dark small">{{ $reminder->nama_tagihan }}</span>
          <span class="badge bg-success-subtle text-success border border-success-subtle" style="font-size:.65rem;">
            <i class="bi bi-tag-fill me-1"></i>{{ ucfirst($reminder->kategori) }}
          </span>
          @if ($reminder->perulangan !== 'tidak')
            <span class="badge bg-primary-subtle text-primary border border-primary-subtle" style="font-size:.65rem;">
              <i class="bi bi-arrow-repeat me-1"></i>{{ ucfirst($reminder->perulangan) }}
            </span>
          @endif
        </div>
      </div>
    </div>
    <button type="button" class="btn-close mt-1" data-bs-dismiss="modal" aria-label="Close"></button>
  </div>

  {{-- ── Hero: Sisa Tagihan ───────────────────────────────────── --}}
  <div class="sisa-hero mb-3">
    <div class="text-muted small mb-1" style="font-size:.82rem;">Sisa Tagihan:</div>
    <div class="fw-bold text-dark" style="font-size:1.65rem;letter-spacing:-.02em;">
      Rp {{ number_format($reminder->sisa_tagihan, 0, ',', '.') }}
    </div>

    <div class="d-flex align-items-center justify-content-between mt-3 pt-2 border-top" style="border-color:#e5e7eb !important;">
      <div class="text-muted small">
        <i class="bi bi-calendar3 me-1"></i>
        Jatuh Tempo:
        <strong class="{{ $isOverdue ? 'text-danger' : 'text-dark' }}">
          {{ $reminder->tanggal_jatuh_tempo ? $reminder->tanggal_jatuh_tempo->locale('id')->translatedFormat('d M Y') : '-' }}
        </strong>
      </div>
      <div>
        @if ($isOverdue)
          <span class="badge bg-danger text-white" style="font-size:.68rem;">
            <i class="bi bi-exclamation-octagon-fill me-1"></i>Overdue
          </span>
        @elseif ($reminder->tanggal_jatuh_tempo && $reminder->tanggal_jatuh_tempo->isToday())
          <span class="badge bg-warning text-dark" style="font-size:.68rem;">
            <i class="bi bi-clock-fill me-1"></i>Hari Ini
          </span>
        @else
          <span class="badge bg-success-subtle text-success border border-success-subtle" style="font-size:.68rem;">
            <i class="bi bi-check-circle me-1"></i>Aktif
          </span>
        @endif
      </div>
    </div>
  </div>

  {{-- ── Hidden Fields ────────────────────────────────────────── --}}
  <input type="hidden" id="id_reminder_bayar" value="{{ $reminder->id }}" data-sisa="{{ $reminder->sisa_tagihan }}" data-perulangan="{{ $reminder->perulangan }}">

  {{-- ── Metode Pelunasan (radio cards) ───────────────────────── --}}
  <div class="mb-3">
    <label class="form-label fw-semibold small text-secondary mb-2">Metode Pelunasan</label>
    <div class="row g-2">
      {{-- Bayar Lunas --}}
      <div class="col-6">
        <div class="mode-card active" id="mode_card_lunas"
             onclick="reminder.selectPaymentMode('lunas', {{ $reminder->sisa_tagihan }})">
          <div class="d-flex align-items-center gap-2 mb-2">
            <div class="radio-circle"><div class="inner"></div></div>
            <span class="fw-bold text-dark" style="font-size:.92rem;">Bayar Lunas</span>
          </div>
          <div class="text-success fw-bold" style="font-size:1.15rem;" id="lunas_amount_display">
            Rp {{ number_format($reminder->sisa_tagihan, 0, ',', '.') }}
          </div>
          <div class="text-muted mt-1" style="font-size:.72rem;">Selesaikan 100% sisa tagihan</div>
        </div>
      </div>

      {{-- Bayar Sebagian --}}
      <div class="col-6">
        <div class="mode-card" id="mode_card_cicil"
             onclick="reminder.selectPaymentMode('cicil', {{ $reminder->sisa_tagihan }})">
          <div class="d-flex align-items-center gap-2 mb-2">
            <div class="radio-circle"><div class="inner"></div></div>
            <span class="fw-bold text-dark" style="font-size:.92rem;">Bayar Sebagian</span>
          </div>
          <input type="text" class="cicil-inline-input" id="cicil_nominal_inline"
                 placeholder="Nominal"
                 onfocus="reminder.selectPaymentMode('cicil', {{ $reminder->sisa_tagihan }})"
                 oninput="reminder.onCicilInlineInput(this, {{ $reminder->sisa_tagihan }})">
          <div class="text-muted mt-1" style="font-size:.72rem;">Atur jatuh tempo sisa berikutnya</div>
        </div>
      </div>
    </div>
  </div>

  {{-- ── Nominal Pembayaran ───────────────────────────────────── --}}
  <div class="mb-3">
    <label for="nominal_bayar" class="form-label fw-semibold small mb-1">
      Nominal Pembayaran <span class="text-danger">*</span>
    </label>

    <div class="input-group input-group-lg border rounded-3 overflow-hidden" style="box-shadow:0 1px 3px rgba(0,0,0,.04);">
      <span class="input-group-text bg-white border-0 fw-bold text-success px-3" style="font-size:1.15rem;">Rp</span>
      <input type="text" class="form-control border-0 fw-bold text-dark py-2 px-1" id="nominal_bayar" name="nominal_bayar"
        value="{{ number_format($reminder->sisa_tagihan, 0, ',', ',') }}"
        style="font-size:1.35rem;"
        placeholder="0" required
        oninput="reminder.onNominalBayarInput(this, {{ $reminder->sisa_tagihan }})">
    </div>

    {{-- Live alerts --}}
    <div id="calc_alert_lunas" class="d-flex align-items-start gap-2 mt-2 p-2 rounded-3" style="background:#f0fdf4;border:1px solid #bbf7d0;">
      <i class="bi bi-check-circle-fill text-success mt-0" style="font-size:1.1rem;flex-shrink:0;"></i>
      <div class="small text-dark">
        Pembayaran ini akan melunasi seluruh tagihan. Status akan diperbarui menjadi <strong>LUNAS</strong>.
      </div>
    </div>

    <div id="calc_alert_cicil" class="d-flex align-items-start gap-2 mt-2 p-2 rounded-3" style="background:#fff7ed;border:1px solid #fed7aa;display:none;">
      <i class="bi bi-pie-chart-fill text-warning mt-0" style="font-size:1.1rem;flex-shrink:0;"></i>
      <div class="small text-dark">
        Pembayaran cicilan. Sisa tagihan setelah ini: <strong class="text-danger" id="text_sisa_nominal">Rp 0</strong>.
      </div>
    </div>

    <div id="calc_alert_over" class="d-flex align-items-start gap-2 mt-2 p-2 rounded-3" style="background:#fef2f2;border:1px solid #fecaca;display:none;">
      <i class="bi bi-exclamation-triangle-fill text-danger mt-0" style="font-size:1.1rem;flex-shrink:0;"></i>
      <div class="small text-dark">
        Nominal melebihi sisa tagihan. Maks: Rp {{ number_format($reminder->sisa_tagihan, 0, ',', '.') }}.
      </div>
    </div>
  </div>

  {{-- ── Tanggal Jatuh Tempo Baru (cicilan) ───────────────────── --}}
  @if ($reminder->perulangan !== 'tidak')
    {{-- Tagihan Berulang: Tanggal jatuh tempo periode ini tidak berubah saat cicilan, periode berikutnya otomatis dibuat setelah lunas --}}
    <div class="mb-3" id="div_jatuh_tempo_baru" style="display:none;">
      <div class="p-3 rounded-3" style="background:#eff6ff;border:1px solid #bfdbfe;">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <label class="form-label fw-bold small mb-0 text-primary">
            <i class="bi bi-arrow-repeat me-1"></i>Tagihan Berulang ({{ ucfirst($reminder->perulangan) }})
          </label>
          <span class="badge text-primary fw-semibold" id="badge_sisa_tempo" style="background:#dbeafe;font-size:.68rem;">
            Sisa: Rp {{ number_format($reminder->sisa_tagihan, 0, ',', '.') }}
          </span>
        </div>
        <div class="small text-muted mb-0">
          Pembayaran cicilan untuk periode berjalan. Tanggal jatuh tempo tetap <strong>{{ $reminder->tanggal_jatuh_tempo ? $reminder->tanggal_jatuh_tempo->locale('id')->translatedFormat('d F Y') : '-' }}</strong>. Tagihan periode berikutnya akan otomatis dibuat setelah tagihan ini dilunasi.
        </div>
      </div>
    </div>
  @else
    {{-- Tagihan Sekali Bayar: opsi perpanjangan tanggal jatuh tempo sisa cicilan --}}
    <div class="mb-3" id="div_jatuh_tempo_baru" style="display:none;">
      <div class="p-3 rounded-3" style="background:#fffbeb;border:1px solid #fde68a;">
        <div class="d-flex align-items-center justify-content-between mb-2">
          <label for="jatuh_tempo_baru" class="form-label fw-bold small mb-0" style="color:#92400e;">
            <i class="bi bi-calendar-event me-1"></i>Jatuh Tempo Baru (Sisa Tagihan)
          </label>
          <span class="badge text-dark fw-semibold" id="badge_sisa_tempo" style="background:#fef3c7;font-size:.68rem;">
            Sisa: Rp {{ number_format($reminder->sisa_tagihan, 0, ',', '.') }}
          </span>
        </div>

        <div class="input-group mb-2">
          <span class="input-group-text bg-white"><i class="bi bi-calendar3 text-primary"></i></span>
          <input type="text" class="form-control bg-white" id="jatuh_tempo_baru" name="jatuh_tempo_baru"
            placeholder="YYYY-MM-DD" value="{{ $defaultNextDue }}" readonly style="cursor:pointer;"
            onclick="$(this).datepicker('show');">
        </div>

        <div class="d-flex flex-wrap gap-1 align-items-center">
          <span class="text-xs text-muted me-1">Ubah Tempo:</span>
          <button type="button" class="chip-tag-btn" onclick="reminder.setQuickDate(7)">+7 Hari</button>
          <button type="button" class="chip-tag-btn" onclick="reminder.setQuickDate(14)">+14 Hari</button>
          <button type="button" class="chip-tag-btn" onclick="reminder.setQuickDate(30)">+1 Bulan</button>
          <button type="button" class="chip-tag-btn" onclick="reminder.setQuickDate('eom')">Akhir Bulan</button>
        </div>
      </div>
    </div>
  @endif

  {{-- ── Metode Pembayaran (Dropdown) ─────────────────────────── --}}
  <div class="mb-3">
    <label for="metode_pembayaran" class="form-label fw-semibold small mb-1">
      Metode Pembayaran <span class="text-muted fw-normal">(Opsional)</span>
    </label>
    <select class="form-select" id="metode_pembayaran" onchange="reminder.onMetodeBayarChange(this)">
      <option value="" selected disabled>— Pilih Metode —</option>
      <option value="Transfer BCA">Transfer BCA</option>
      <option value="Transfer Mandiri">Transfer Mandiri</option>
      <option value="Transfer BRI">Transfer BRI</option>
      <option value="Transfer BNI">Transfer BNI</option>
      <option value="QRIS">QRIS</option>
      <option value="Tunai">Tunai</option>
      <option value="E-Wallet (GoPay/OVO/Dana)">E-Wallet (GoPay/OVO/Dana)</option>
      <option value="Lainnya">Lainnya</option>
    </select>
  </div>

  {{-- ── Catatan Pembayaran ───────────────────────────────────── --}}
  <div class="mb-3">
    <label for="catatan_bayar" class="form-label fw-semibold small mb-1">
      Catatan / Metode Pembayaran <span class="text-muted fw-normal">(Opsional)</span>
    </label>
    <textarea class="form-control" id="catatan_bayar" name="catatan_bayar" rows="2"
      placeholder="Contoh: Transfer Bank BCA / Bukti Ref #12345"></textarea>
  </div>

  {{-- ── Riwayat Pembayaran (Collapsible) ─────────────────────── --}}
  @if ($reminder->payments->count() > 0)
    <div class="mb-3">
      <div class="border rounded-3 overflow-hidden bg-white">
        <div class="py-2 px-3 d-flex justify-content-between align-items-center"
             role="button" style="background:#f9fafb;"
             onclick="$('#history_collapse').slideToggle(200); $('#icon_history_toggle').toggleClass('rotate-180');">
          <div class="small fw-semibold text-secondary d-flex align-items-center gap-2">
            <i class="bi bi-clock-history text-primary"></i>
            <span>Riwayat Pembayaran ({{ $reminder->payments->count() }} transaksi)</span>
          </div>
          <div class="d-flex align-items-center gap-2">
            <span class="badge bg-success-subtle text-success small fw-semibold" style="font-size:.68rem;">
              Terbayar: Rp {{ number_format($terbayar, 0, ',', '.') }}
            </span>
            <i class="bi bi-chevron-down text-muted small transition-all" id="icon_history_toggle"></i>
          </div>
        </div>
        <div id="history_collapse" style="display:none;">
          <div class="table-responsive" style="max-height:150px;overflow-y:auto;">
            <table class="table table-sm table-hover mb-0" style="font-size:.75rem;">
              <thead class="table-light sticky-top">
                <tr>
                  <th class="ps-3">Waktu</th>
                  <th>Sumber</th>
                  <th>Catatan</th>
                  <th class="text-end pe-3">Nominal</th>
                </tr>
              </thead>
              <tbody>
                @foreach ($reminder->payments as $p)
                  <tr>
                    <td class="ps-3 text-muted">{{ $p->tanggal_bayar ? $p->tanggal_bayar->format('d/m/Y H:i') : '-' }}</td>
                    <td><span class="badge bg-light text-dark border">{{ strtoupper($p->sumber) }}</span></td>
                    <td class="text-truncate" style="max-width:130px;" title="{{ $p->catatan }}">{{ $p->catatan ?: '-' }}</td>
                    <td class="text-end pe-3 fw-bold text-success">+ Rp {{ number_format($p->nominal_bayar, 0, ',', '.') }}</td>
                  </tr>
                @endforeach
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  @endif

  {{-- ── Info Integrasi ───────────────────────────────────────── --}}
  <div class="d-flex align-items-start gap-2 p-2 rounded-3 mb-3" style="background:#eff6ff;border:1px solid #bfdbfe;">
    <i class="bi bi-shield-check text-primary mt-0" style="font-size:1.1rem;flex-shrink:0;"></i>
    <div class="small text-dark">
      Pembayaran ini akan <strong>otomatis dicatat ke Pengeluaran</strong> pada Buku Kas Keuangan.
      @if ($reminder->perulangan !== 'tidak')
        <div class="text-muted mt-1" style="font-size:.7rem;">
          <i class="bi bi-arrow-repeat me-1"></i>Tagihan berulang (<strong>{{ ucfirst($reminder->perulangan) }}</strong>): tagihan periode berikutnya akan otomatis dibuat setelah lunas.
        </div>
      @endif
    </div>
  </div>

  {{-- ── Footer Actions ───────────────────────────────────────── --}}
  <div class="d-flex flex-column flex-sm-row justify-content-between align-items-center gap-2 pt-3 border-top">
    <div class="text-muted small">
      Total Bayar:
      <strong class="text-success fw-bold" id="footer_nominal_display" style="font-size:1.15rem;">
        Rp {{ number_format($reminder->sisa_tagihan, 0, ',', '.') }}
      </strong>
    </div>
    <div class="d-flex gap-2">
      <button type="button" class="btn btn-light border px-3" data-bs-dismiss="modal">
        Batal
      </button>
      <button type="button" class="btn btn-success px-4 fw-semibold" id="btn_submit_bayar"
              onclick="reminder.submitBayar(this)" style="box-shadow:0 2px 8px rgba(22,163,74,.25);">
        <i class="bi bi-check2-circle me-1"></i><span id="btn_text_bayar">Bayar Sekarang</span>
      </button>
    </div>
  </div>

</div>
