const container = document.querySelector(".container"),
    pwShowHide = document.querySelectorAll(".showHidePw"),
    pwFields = document.querySelectorAll(".password"),
    signUp = document.querySelector(".signup-link"),
    login = document.querySelector(".login-link");
// js code to show/hide password and change icon
pwShowHide.forEach((eyeIcon) => {
    eyeIcon.addEventListener("click", () => {
        pwFields.forEach((pwField) => {
            if (pwField.type === "password") {
                pwField.type = "text";
                pwShowHide.forEach((icon) => {
                    icon.classList.replace("uil-eye-slash", "uil-eye");
                });
            } else {
                pwField.type = "password";
                pwShowHide.forEach((icon) => {
                    icon.classList.replace("uil-eye", "uil-eye-slash");
                });
            }
        });
    });
});
// js code to appear signup and login form
signUp.addEventListener("click", (e) => {
    e.preventDefault();
    container.classList.add("active");
});
login.addEventListener("click", (e) => {
    e.preventDefault();
    container.classList.remove("active");
});

$(document).ready(function () {
    $("#login").on("click", function (e) {
        e.preventDefault();
        const login_email = $(this).closest(".login").find("#email").val();
        const login_password = $(this).closest(".login").find("#password").val();

        if (login_email == "" || login_password == "") {
            Swal.fire({
                icon: 'error',
                title: 'Oops...',
                text: 'Please fill in all fields!',
            });
            return;
        }

        $.ajax({
            url: "../backend/users/login.php",
            method: "POST",
            data: {
                email: login_email,
                password: login_password
            },
            success: function (response) {
                const responseData = JSON.parse(response);
                if (responseData.status === "success") {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success',
                        text: responseData.message,
                    }).then((isConfirmed) => {
                        if (isConfirmed.isConfirmed && responseData.role_id === 1) {
                            window.location.href = "./menu.php";
                        }else{
                            window.location.href = "../admin/index.php";
                        }
                    });
                } else if (responseData.status === "error") {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: responseData.message,
                    });
                }
            },
            error: function () {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'An error occurred while processing your request. Please try again.',
                });
            }
        })
    })
})