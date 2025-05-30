<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!-- Top DSWD-colored bar -->
<div style="height: 4px; background: linear-gradient(to right, #CE1126, #0038A8, #FFD100);"></div>

<header id="header" class="header fixed-top d-flex align-items-center bg-white shadow-sm px-4 py-2" style="z-index: 1030;">
    <div class="d-flex align-items-center justify-content-between w-100">
        <!-- Logo + Name -->
        <a href="/CleanAid-IT322-PIT/view/admin/dashboard.php" class="d-flex align-items-center text-decoration-none">
            <img src="../../assets/img/dswd-logo3.png" alt="DSWD Logo" style="height: 35px;" class="me-2">
            <h5 class="m-0 fw-bold text-dark">CleanAid</h5>
        </a>

        <!-- Right: Profile -->
        <nav class="header-nav ms-auto">
            <ul class="d-flex align-items-center list-unstyled mb-0">
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle d-flex align-items-center text-dark" href="#" data-bs-toggle="dropdown">
                        <i class="bi bi-person-circle fs-4 me-2"></i>
                        <span><?= $_SESSION['authUser']['fullName'] ?? 'Admin User' ?></span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="user_profile.php"><i class="bi bi-person me-2"></i> My Profile</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="/CleanAid-IT322-PIT/login.php"><i class="bi bi-box-arrow-right me-2"></i> Sign Out</a></li>
                    </ul>
                </li>
            </ul>
        </nav>
    </div>
</header>
