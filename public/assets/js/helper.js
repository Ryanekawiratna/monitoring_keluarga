var helper = {
    url: (path) => {
        if (path) {
            return window.location.origin + "/" + path + "/";
        } else {
            return window.location.origin;
        }
    },
};

$(document).ready(function () {
    $.ajaxSetup({
        headers: {
            "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
        },
    });
});
