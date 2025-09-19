<nav>
    <div class="img-logo">
        <img src="../assets/nutri_log.jpeg" alt="logo">
    </div>

    <div class="right-container">
        <div class="menu-toggle"><i class="fa-solid fa-bars"></i></div>
        <div class="drop-down">
            <a href="./index.php" class="<?php echo (basename($_SERVER['PHP_SELF']) == 'index.php') ? 'active' : ''; ?>"><i class="fa-solid fa-house"></i> Home</a>

            <a href="./announcements.php"
                class="<?php echo (basename($_SERVER['PHP_SELF']) == 'announcements.php') ? 'active' : ''; ?>"><i class="fa-solid fa-bullhorn"></i> Announcements</a>

            <a href="./modules.php"
                class="<?php echo (basename($_SERVER['PHP_SELF']) == 'modules.php') ? 'active' : ''; ?>"><i class="fa-solid fa-book"></i>Modules</a>

            <a href="./events.php"  
                class="<?php echo (basename($_SERVER['PHP_SELF']) == 'events.php') ? 'active' : ''; ?>"><i class="fa-solid fa-calendar-days"></i> Events</a>

            <?php if (isset($_SESSION["user_id"])) { ?>
                <div class="arrow-down">
                    <i class="fa-solid fa-sort-down"></i>
                    <div class="settings-container">
                        <div>
                            <a href="./profile.php"
                                id="profile"
                                class="<?php echo (basename($_SERVER['PHP_SELF']) == 'profile.php') ? 'active' : ''; ?>">Profile</a>
                        </div>
                        <div>
                            <a href="./logout.php">
                                Log out
                            </a>
                        </div>
                    </div>
                </div>

            <?php } else { ?>
                <a href="login.php"
                    class="<?php echo (basename($_SERVER['PHP_SELF']) == 'login.php') ? 'active' : ''; ?>">
                    <i class="fa-solid fa-user"></i>
                    Login</a>
            <?php } ?>

        </div>
    </div>
    </div>
</nav>