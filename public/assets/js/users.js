var users = {
    module: () => {
        return "users";
    },

    showPassword: (elm) => {
        let passwordInput = $("input#password");

        const type =
            passwordInput.attr("type") === "password" ? "text" : "password";
        passwordInput.attr("type", type);

        const eyeIcon = $("i#eyeIcon");

        if (type === "text") {
            eyeIcon.removeClass("bi-eye-slash-fill");
            eyeIcon.addClass("bi-eye-fill");
        } else {
            eyeIcon.removeClass("bi-eye-fill");
            eyeIcon.addClass("bi-eye-slash-fill");
        }
    },
};

$(document).ready(function () {});
