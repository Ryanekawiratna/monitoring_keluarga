<div class="row justify-content-center">
  <div class="col-12 col-lg-12 col-xl-12">

    <!-- Header -->
    <div class="text-center mb-4">
      <h4 class="fw-bold mb-1">Form Tambah Transaksi</h4>
      <p class="text-muted mb-0">
        Lengkapi informasi transaksi di bawah ini
      </p>
    </div>

    <!-- Form Card -->
    <div class="card border-0 shadow-sm">
      <div class="card-body p-3 p-md-4">

        <!-- Informasi Utama -->
        <div class="mb-4">
          <h6 class="fw-bold mb-3">
            Informasi Transaksi
          </h6>

          <div class="row g-3">

            <!-- Tanggal -->
            <div class="col-12 col-md-6">
              <label for="tgl" class="form-label fw-semibold">
                Tanggal
              </label>

              <input type="text" class="form-control" id="tgl" name="tgl" value=""
                placeholder="Masukan Tanggal">
            </div>

            <!-- Jenis Transaksi -->
            <div class="col-12 col-md-6">
              <label for="jenis" class="form-label fw-semibold">
                Jenis Transaksi
                <span class="text-danger">*</span>
              </label>

              <select class="form-control" id="jenis" name="jenis" required>
                <option value="" selected>
                  -- Pilih Jenis --
                </option>

                <option value="masuk">
                  Pemasukan
                </option>

                <option value="keluar">
                  Pengeluaran
                </option>
              </select>
            </div>

          </div>
        </div>


        <!-- Nominal -->
        <div class="mb-4">

          <label for="nominal" class="form-label fw-semibold">
            Nominal Transaksi
            <span class="text-danger">*</span>
          </label>

          <div class="input-group input-group-lg">

            <span class="input-group-text fw-semibold">
              Rp
            </span>

            <input type="text" class="form-control" id="nominal" name="nominal" min="0"
              placeholder="0" required oninput="trans.numberFormat(this)">
          </div>

          <div class="form-text">
            Masukkan nominal transaksi dalam Rupiah.
          </div>

        </div>


        <!-- Detail Transaksi -->
        <div class="mb-4">

          <h6 class="fw-bold mb-3">
            Detail Transaksi
          </h6>

          <div class="row g-3">

            <!-- Kategori -->
            <div class="col-12 col-md-5">

              <label for="kategori" class="form-label fw-semibold">
                Kategori
                <span class="text-danger">*</span>
              </label>

              <select class="form-control" id="kategori" name="kategori" required>
                <option value="" selected disabled>
                  -- Pilih Kategori --
                </option>

                <?php foreach ($kategori as $key => $vk) { ?>
                  <option value="<?= $vk['nama_kategori'] ?>">
                    <?= $vk['nama_kategori'] . ' - ' . $vk['jenis'] ?>
                  </option>
                <?php } ?>

              </select>

            </div>


            <!-- Keterangan -->
            <div class="col-12 col-md-7">

              <label for="keterangan" class="form-label fw-semibold">
                Keterangan
                <span class="text-danger">*</span>
              </label>

              <textarea class="form-control" id="keterangan" name="keterangan" rows="3"
                placeholder="Masukkan keterangan transaksi" required></textarea>

            </div>

          </div>

        </div>


        <!-- Divider -->
        <hr class="my-4">


        <!-- Action -->
        <div class="d-flex flex-column flex-sm-row justify-content-end gap-2">

          <button type="button" class="btn btn-light border px-4 order-2 order-sm-1" data-bs-dismiss="modal">
            Kembali
          </button>

          <button type="button" class="btn btn-primary px-4 order-1 order-sm-2"
            onclick="trans.submitTransaksi(this)">
            Simpan Transaksi
          </button>

        </div>

      </div>
    </div>

  </div>
</div>