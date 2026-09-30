var reminder = {
    module: () => {
        return "reminder";
    },

    numberFormat: (elm) => {
        let number = $(elm).val();
        number = number.replace(/,/g, "");
        number = number.replace(/\D/g, "");

        if (number === "") {
            $(elm).val("");
            return;
        }

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

    bboxReminder: (elm, id = null) => {
        let params = {};
        if (id) {
            params.id = id;
        }

        $.ajax({
            type: "POST",
            dataType: "HTML",
            data: params,
            url: helper.url(reminder.module()) + "bbox",
            beforeSend: function () {
                showLoading("Sedang memproses...");
            },
            error: function () {
                closeLoading();
                showError("Gagal memuat form tagihan");
            },
            success: function (resp_view) {
                closeLoading();
                bootbox
                    .dialog({
                        message: resp_view,
                        closeButton: true,
                    })
                    .on("shown.bs.modal", function () {
                        const dialog = $(this).find(".modal-dialog");
                        dialog.css("max-width", "55%");

                        // Datepicker jatuh tempo
                        $(this).find("input#tanggal_jatuh_tempo").datepicker({
                            dateFormat: "yy-mm-dd",
                        });

                        // Select2 kategori
                        $(this)
                            .find("select#kategori")
                            .select2({
                                dropdownParent: $(this),
                            });
                    });
            },
        });
    },

    submitReminder: (elm) => {
        let params = {
            id: $("input#id_reminder").val(),
            nama_tagihan: $("input#nama_tagihan").val(),
            jenis_transaksi: $("select#jenis_transaksi option:selected").val(),
            total_nominal: reminder.numberUnFormat(
                $("input#total_nominal").val(),
            ),
            tanggal_jatuh_tempo: $("input#tanggal_jatuh_tempo").val(),
            wa_number: $("input#wa_number").val(),
            kategori: $("select#kategori option:selected").val(),
            perulangan: $("select#perulangan option:selected").val(),
            keterangan: $("textarea#keterangan").val(),
        };

        if (
            !params.nama_tagihan ||
            !params.total_nominal ||
            !params.tanggal_jatuh_tempo ||
            !params.kategori
        ) {
            showError("Mohon lengkapi field yang bertanda bintang (*)");
            return;
        }

        $.ajax({
            type: "POST",
            dataType: "JSON",
            data: params,
            url: helper.url(reminder.module()) + "submit",
            beforeSend: function () {
                showLoading("Menyimpan data tagihan...");
            },
            error: function () {
                closeLoading();
                showError("Terjadi kesalahan saat menyimpan data");
            },
            success: function (resp) {
                closeLoading();
                if (resp.is_valid) {
                    showSuccess(resp.message || "Tagihan berhasil disimpan");
                    setTimeout(() => {
                        window.location.reload();
                    }, 800);
                } else {
                    showError(resp.message || "Gagal menyimpan tagihan");
                }
            },
        });
    },

    bboxBayar: (elm, id) => {
        $.ajax({
            type: "POST",
            dataType: "HTML",
            data: { id: id },
            url: helper.url(reminder.module()) + "bboxBayar",
            beforeSend: function () {
                showLoading("Memuat form pembayaran...");
            },
            error: function () {
                closeLoading();
                showError("Gagal membuka form pembayaran");
            },
            success: function (resp_view) {
                closeLoading();
                bootbox
                    .dialog({
                        message: resp_view,
                        closeButton: false,
                    })
                    .on("shown.bs.modal", function () {
                        const dialog = $(this).find(".modal-dialog");
                        dialog.addClass("modal-dialog-centered");
                        dialog.css({
                            "max-width": "680px",
                            width: "94%",
                            margin: "1.75rem auto",
                        });
                        $(this).find(".modal-content").css({
                            "border-radius": "18px",
                            border: "none",
                            "box-shadow":
                                "0 25px 50px -12px rgba(15, 23, 42, 0.25)",
                            overflow: "hidden",
                        });

                        if ($(this).find("input#jatuh_tempo_baru").length) {
                            $(this).find("input#jatuh_tempo_baru").datepicker({
                                dateFormat: "yy-mm-dd",
                                minDate: 0,
                            });
                        }
                    });
            },
        });
    },

    selectPaymentMode: (mode, sisaTagihan) => {
        if (mode === "lunas") {
            $("#mode_card_lunas").addClass("active");
            $("#mode_card_cicil").removeClass("active");
            // Set nominal = sisa tagihan (lunas)
            reminder.setBayarLunas(sisaTagihan);
            // Clear inline cicil input
            $("#cicil_nominal_inline").val("");
        } else {
            $("#mode_card_cicil").addClass("active");
            $("#mode_card_lunas").removeClass("active");
            // Read inline input or default to empty
            let inlineVal =
                reminder.numberUnFormat($("#cicil_nominal_inline").val()) || 0;
            if (inlineVal > 0 && inlineVal < sisaTagihan) {
                let formatted = new Intl.NumberFormat("en-US", {
                    maximumFractionDigits: 0,
                }).format(inlineVal);
                $("input#nominal_bayar").val(formatted);
                reminder.onNominalBayarInput(
                    $("input#nominal_bayar")[0],
                    sisaTagihan,
                );
            } else {
                // Clear and let user type
                $("input#nominal_bayar").val("");
                reminder.onNominalBayarInput(
                    $("input#nominal_bayar")[0],
                    sisaTagihan,
                );
            }
            $("#cicil_nominal_inline").focus();
        }
    },

    onCicilInlineInput: (elm, sisaTagihan) => {
        // Format the inline input
        reminder.numberFormat(elm);
        let val = reminder.numberUnFormat($(elm).val()) || 0;
        // Sync to main nominal input
        if (val > 0) {
            let formatted = new Intl.NumberFormat("en-US", {
                maximumFractionDigits: 0,
            }).format(val);
            $("input#nominal_bayar").val(formatted);
            reminder.onNominalBayarInput(
                $("input#nominal_bayar")[0],
                sisaTagihan,
            );
        }
        // Auto-activate cicil mode
        if (!$("#mode_card_cicil").hasClass("active")) {
            $("#mode_card_cicil").addClass("active");
            $("#mode_card_lunas").removeClass("active");
        }
    },

    onMetodeBayarChange: (elm) => {
        let val = $(elm).val();
        if (val && val !== "Lainnya") {
            let current = $("textarea#catatan_bayar").val().trim();
            if (!current) {
                $("textarea#catatan_bayar").val(val);
            } else if (!current.toLowerCase().includes(val.toLowerCase())) {
                $("textarea#catatan_bayar").val(val + " - " + current);
            }
        }
    },

    setBayarLunas: (nominal) => {
        let formatted = new Intl.NumberFormat("en-US", {
            maximumFractionDigits: 0,
        }).format(parseInt(nominal, 10));
        $("input#nominal_bayar").val(formatted);
        reminder.onNominalBayarInput($("input#nominal_bayar")[0], nominal);
    },

    clearNominal: () => {
        $("input#nominal_bayar").val("").focus();
        let sisaTagihan = parseInt(
            $("#id_reminder_bayar").data("sisa") || 0,
            10,
        );
        reminder.onNominalBayarInput($("input#nominal_bayar")[0], sisaTagihan);
    },

    setQuickDate: (daysOrType) => {
        let target = new Date();
        if (daysOrType === "eom") {
            target = new Date(target.getFullYear(), target.getMonth() + 1, 0);
        } else {
            target.setDate(target.getDate() + parseInt(daysOrType, 10));
        }

        let yyyy = target.getFullYear();
        let mm = String(target.getMonth() + 1).padStart(2, "0");
        let dd = String(target.getDate()).padStart(2, "0");
        let dateStr = `${yyyy}-${mm}-${dd}`;

        $("input#jatuh_tempo_baru").val(dateStr);
    },

    appendCatatan: (text) => {
        let current = $("textarea#catatan_bayar").val().trim();
        if (!current) {
            $("textarea#catatan_bayar").val(text);
        } else if (!current.includes(text)) {
            $("textarea#catatan_bayar").val(current + " - " + text);
        }
        $("textarea#catatan_bayar").focus();
    },

    onNominalBayarInput: (elm, sisaTagihan) => {
        reminder.numberFormat(elm);
        let val = reminder.numberUnFormat($(elm).val()) || 0;
        let formattedVal = new Intl.NumberFormat("id-ID").format(val);

        // Update footer and preview
        $("#footer_nominal_display").text("Rp " + formattedVal);

        if (val <= 0) {
            $("#calc_alert_lunas").hide();
            $("#calc_alert_cicil").hide();
            $("#calc_alert_over").hide();
            $("#div_jatuh_tempo_baru").slideUp(200);
            $("#btn_text_bayar").text("Bayar Tagihan");
            return;
        }

        if (val >= sisaTagihan) {
            // Pelunasan penuh (Lunas)
            if (val > sisaTagihan) {
                $("#calc_alert_over").show();
                $("#calc_alert_lunas").hide();
                $("#calc_alert_cicil").hide();
            } else {
                $("#calc_alert_lunas").show();
                $("#calc_alert_cicil").hide();
                $("#calc_alert_over").hide();
            }
            $("#div_jatuh_tempo_baru").slideUp(200);
            $("#mode_card_lunas").addClass("active");
            $("#mode_card_cicil").removeClass("active");
            $("#btn_text_bayar").text("Bayar Lunas Sekarang");
        } else {
            // Cicilan sebagian
            let sisaBaru = sisaTagihan - val;
            let formattedSisaBaru = new Intl.NumberFormat("id-ID").format(
                sisaBaru,
            );

            $("#calc_alert_lunas").hide();
            $("#calc_alert_over").hide();
            $("#calc_alert_cicil").show();
            $("#text_sisa_nominal").text("Rp " + formattedSisaBaru);
            $("#badge_sisa_tempo").text("Sisa: Rp " + formattedSisaBaru);

            $("#div_jatuh_tempo_baru").slideDown(200);
            $("#mode_card_cicil").addClass("active");
            $("#mode_card_lunas").removeClass("active");
            $("#btn_text_bayar").text("Bayar Sebagian (Cicil)");
        }
    },

    submitBayar: (elm) => {
        let nominal = reminder.numberUnFormat($("input#nominal_bayar").val());
        if (!nominal || nominal <= 0) {
            showError("Masukkan nominal pembayaran yang valid");
            $("input#nominal_bayar").focus();
            return;
        }

        let sisaTagihan = parseInt(
            $("#id_reminder_bayar").data("sisa") || 0,
            10,
        );
        let perulangan = $("#id_reminder_bayar").data("perulangan") || "tidak";
        let jatuhTempoBaru = $("input#jatuh_tempo_baru").length ? $("input#jatuh_tempo_baru").val() : null;

        // Validasi jika mencicil tapi belum memilih tanggal jatuh tempo sisa (hanya untuk tagihan tidak berulang)
        if (perulangan === "tidak" && nominal < sisaTagihan && !jatuhTempoBaru) {
            showError(
                "Harap tentukan tanggal jatuh tempo baru untuk sisa tagihan",
            );
            $("input#jatuh_tempo_baru").focus();
            return;
        }

        let params = {
            id_reminder: $("input#id_reminder_bayar").val(),
            nominal_bayar: nominal,
            jatuh_tempo_baru: perulangan === "tidak" ? jatuhTempoBaru : null,
            catatan: $("textarea#catatan_bayar").val(),
        };

        const $btn = $(elm);
        const originalHtml = $btn.html();
        $btn.prop("disabled", true).html(
            '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> Memproses...',
        );

        $.ajax({
            type: "POST",
            dataType: "JSON",
            data: params,
            url: helper.url(reminder.module()) + "submitBayar",
            beforeSend: function () {
                showLoading("Memproses pembayaran...");
            },
            error: function () {
                closeLoading();
                $btn.prop("disabled", false).html(originalHtml);
                showError("Gagal memproses pembayaran");
            },
            success: function (resp) {
                closeLoading();
                if (resp.is_valid) {
                    showSuccess(resp.message || "Pembayaran berhasil disimpan");
                    bootbox.hideAll();
                    setTimeout(() => {
                        window.location.reload();
                    }, 800);
                } else {
                    $btn.prop("disabled", false).html(originalHtml);
                    showError(resp.message || "Gagal memproses pembayaran");
                }
            },
        });
    },

    hapusReminder: (elm, id) => {
        bootbox.confirm({
            message:
                "Apakah Anda yakin ingin menghapus jadwal tagihan ini? Riwayat transaksi yang sudah tercatat di laporan keuangan tidak akan terhapus.",
            buttons: {
                confirm: {
                    label: "Ya, Hapus",
                    className: "btn-danger",
                },
                cancel: {
                    label: "Batal",
                    className: "btn-light",
                },
            },
            callback: function (result) {
                if (result) {
                    $.ajax({
                        type: "POST",
                        dataType: "JSON",
                        data: { id_reminder: id },
                        url: helper.url(reminder.module()) + "hapus",
                        beforeSend: function () {
                            showLoading("Sedang menghapus...");
                        },
                        error: function () {
                            closeLoading();
                            showError("Gagal menghapus tagihan");
                        },
                        success: function (resp) {
                            closeLoading();
                            if (resp.is_valid) {
                                showSuccess(
                                    resp.message || "Tagihan berhasil dihapus",
                                );
                                setTimeout(() => {
                                    window.location.reload();
                                }, 700);
                            } else {
                                showError(
                                    resp.message || "Gagal menghapus tagihan",
                                );
                            }
                        },
                    });
                }
            },
        });
    },

    toggleStatus: (elm, id) => {
        $.ajax({
            type: "POST",
            dataType: "JSON",
            data: { id_reminder: id },
            url: helper.url(reminder.module()) + "toggleStatus",
            beforeSend: function () {
                showLoading("Mengubah status...");
            },
            error: function () {
                closeLoading();
                showError("Gagal mengubah status tagihan");
            },
            success: function (resp) {
                closeLoading();
                if (resp.is_valid) {
                    showSuccess(resp.message || "Status berhasil diubah");
                    setTimeout(() => {
                        window.location.reload();
                    }, 600);
                } else {
                    showError(resp.message || "Gagal mengubah status");
                }
            },
        });
    },
};
