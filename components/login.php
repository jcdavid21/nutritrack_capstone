<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="stylesheet" href="https://unicons.iconscout.com/release/v4.0.0/css/line.css" />
    <link rel="stylesheet" href="../styles/login.css" />
    <link rel="stylesheet" href="../styles/navbar.css">
    <link rel="stylesheet" href="../styles/footer.css">
    <link rel="stylesheet" href="../styles/general.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <title>Login & Registration Form</title>
</head>

<body>
    <?php include "navbar.php"; ?>
    <div class="center">
        <div class="container">
            <div class="forms">
                <div class="form login">
                    <span class="title">Login</span>
                    <form action="#" method="POST" id="loginForm">
                        <div class="input-field">
                            <input type="text" placeholder="Enter your username"
                                id="username" required />
                            <i class="uil uil-user icon"></i>
                        </div>
                        <div class="input-field">
                            <input type="password" class="password"
                                id="password" placeholder="Enter your password" required />
                            <i class="uil uil-lock icon"></i>
                            <i class="uil uil-eye-slash showHidePw"></i>
                        </div>
                        <div class="checkbox-text">
                            <div class="checkbox-content">
                                <input type="checkbox" id="logCheck" />
                                <label for="logCheck" class="text">Remember me</label>
                            </div>
                        </div>
                        <div class="input-field button" id="login">
                            <input type="submit" value="Login" />
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <?php include 'footer.php'; ?>
    <script src="../js/login.js"></script>
</body>

</html>