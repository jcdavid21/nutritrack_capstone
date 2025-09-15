const container = document.querySelector(".container"),
    pwShowHide = document.querySelectorAll(".showHidePw"),
    pwFields = document.querySelectorAll(".password");
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


$(document).ready(function () {
    $("#loginForm").on("submit", function (e) {
        e.preventDefault();
        const username = $(this).closest(".login").find("#username").val();
        const login_password = $(this).closest(".login").find("#password").val();

        if (username == "" || login_password == "") {
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
                username: username,
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
                        if(isConfirmed.isConfirmed && responseData.role_id === 2){
                            window.location.href = "../admin/dashboard.php";
                        }else if(isConfirmed.isConfirmed && responseData.role_id === 3){
                            Swal.fire({
                                icon: 'warning',
                                title: 'No Permission',
                                text: 'Your account does not have permission to access any dashboard.',
                            });
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