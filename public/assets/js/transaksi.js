var trans = {
    module: () => {
        return "transaksi";
    },

    numberFormat: (elm) => {
        let number = $(elm).val();

        number = number.replace(/,/g, "");

        number = number.replace(/\D/g, "");

        if (number === "") {
            $(elm).val("");
            return;
        }

        // Format ribuan
        let result = new Intl.NumberFormat("en-US", {
            maximumFractionDigits: 0,
        }).format(parseInt(number, 10));

        $(elm).val(result);
    },

    numberUnFormat: (number) => {
        if (typeof number === "number") {
            return number;
        }

        if (typeof number !== "string") {
            return null;
        }

        return Number(number.replace(/,/g, ""));
    },

    bboxTransaksi: (elm) => {
        let params = {};
        $.ajax({
            type: "POST",
            dataType: "HTML",
            // data: params,
            url: helper.url(trans.module()) + "bbox",
            beforeSend: function () {
                showLoading("Sedang memproses...");
            },

            error: function () {
                closeLoading();
                showError("Proses Gagal");
            },

            success: function (resp_view) {
                closeLoading();
                showSuccess("Proses Berhasil");

                bootbox
                    .dialog({
                        message: resp_view,
                        closeButton: true,
                    })
                    .on("shown.bs.modal", function () {
                        const dialog = $(this).find(".modal-dialog");
                        dialog.css("max-width", "50%");

                        // 1. Datepicker
                        $(this).find("input#tgl").datepicker({
                            dateFormat: "dd-M-yy",
                        });

                        // 2. Select2 (Tambahkan dropdownParent agar tidak ketutup modal)
                        $(this)
                            .find("select#kategori")
                            .select2({
                                dropdownParent: $(this), // Mengikat dropdown ke modal bootbox yang sedang aktif
                            });
                    });
            },
        });
    },

    submitTransaksi: (elm) => {
        // elm.preventDefault();
        let params = {
            tanggal: $("input#tgl").val(),
            jenis_transaksi: $("select#jenis option:selected").val(),
            keterangan: $("textarea#keterangan").val(),
            kategori: $("select#kategori option:selected").val(),
            nominal: trans.numberUnFormat($("input#nominal").val()),
        };

        console.log($("input#nominal").val());

        $.ajax({
            type: "POST",
            dataType: "JSON",
            data: params,

            url: helper.url(trans.module()) + "submitTransaksi",
            beforeSend: function () {
                showLoading("Sedang memproses...");
            },
            error: function (xhr, status, error) {
                closeLoading();
                showError("Proses Gagal");
                console.log("Status:", status);
                console.log("Error:", error);
                console.log("Respons Mentah Server:", xhr.responseText); // <-- Lihat ini di Console browser
            },
            success: function (resp) {
                closeLoading();
                console.log("tes", resp);

                if (resp.is_valid) {
                    showSuccess("Proses Berhasil");
                    window.location.reload();
                } else {
                    showError("Proses Gagal");
                }
            },
        });
    },

    hapusTransaksi: (elm) => {
        let idTransaksi = $(elm).attr("id_transaksi");

        bootbox.confirm({
            message: "Apakah anda yakin ingin menghapus data ini?",
            buttons: {
                confirm: {
                    label: "Ya",
                    className: "btn-success",
                },
                cancel: {
                    label: "Tidak",
                    className: "btn-danger",
                },
            },
            callback: function (result) {
                $.ajax({
                    type: "POST",
                    dataType: "JSON",
                    data: {
                        id_transaksi: idTransaksi,
                    },
                    url: helper.url(trans.module()) + "hapusTransaksi",
                    beforeSend: function () {
                        showLoading("Sedang menghapus...");
                    },
                    error: function (xhr) {
                        closeLoading();
                        showError("Gagal menghapus data");
                    },
                    success: function (resp) {
                        closeLoading();
                        if (resp.is_valid) {
                            showSuccess("Data berhasil dihapus");
                            window.location.reload(); // Refresh halaman setelah sukses
                        } else {
                            showError(resp.message || "Gagal menghapus data");
                        }
                    },
                });
            },
        });
    },
};

$(document).ready(function () {});
