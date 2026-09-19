const notyf = new Notyf({
    duration: 3000,
    dismissible: false,

    position: {
        x: "right",
        y: "top",
    },

    types: [
        // ========================================
        // LOADING
        // ========================================
        {
            type: "loading",
            className: "notyf__toast--loading",
            backgroundColor: "#ffffff",
            ripple: false,
            icon: false,
        },

        // ========================================
        // WARNING
        // ========================================
        {
            type: "warning",
            className: "notyf__toast--warning",
            backgroundColor: "#f59e0b",
            ripple: true,
            icon: {
                className: "notyf__icon--warning",
                tagName: "i",
                text: "!",
            },
        },
    ],
});

// ========================================
// LOADING
// ========================================

let loadingNotification = null;

function showLoading(message = "Memproses...") {
    closeLoading();

    loadingNotification = notyf.open({
        type: "loading",
        duration: 0,
        dismissible: false,

        message: `
            <span class="notyf-loading">
                <span class="notyf-spinner"></span>
                <span>${message}</span>
            </span>
        `,
    });

    return loadingNotification;
}

// ========================================
// CLOSE LOADING
// ========================================

function closeLoading() {
    if (loadingNotification) {
        notyf.dismiss(loadingNotification);
        loadingNotification = null;
    }
}

// ========================================
// SUCCESS
// ========================================

function showSuccess(message = "Berhasil!") {
    closeLoading();

    notyf.success(message);
}

// ========================================
// ERROR
// ========================================

function showError(message = "Terjadi kesalahan.") {
    closeLoading();

    notyf.error(message);
}

// ========================================
// WARNING
// ========================================

function showWarning(message = "Perhatian!") {
    closeLoading();

    notyf.open({
        type: "warning",
        message: message,
    });
}
