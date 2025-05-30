<!-- Sidebar -->
<aside id="sidebar" class="bg-white border-end position-fixed top-0 start-0 vh-100 shadow-sm" style="width: 260px; padding-top: 64px; z-index: 1020;">

  <ul class="sidebar-nav list-unstyled mb-0" id="sidebar-nav">

    <!-- Section: MAIN -->
    <li class="px-3 text-uppercase text-muted small fw-bold mt-3 mb-2">Main</li>

    <li class="nav-item">
      <a href="dashboard.php" class="nav-link d-block px-4 py-2 rounded-0 <?= basename($_SERVER['PHP_SELF']) === 'dashboard.php' ? 'fw-bold bg-light border-start border-4 border-primary text-dark' : 'text-dark' ?>">
        <i class="bi bi-house-door me-2"></i> Dashboard
      </a>
    </li>

    <!-- Section: Data Management -->
    <li class="px-3 text-uppercase text-muted small fw-bold mt-4 mb-2">Data Management</li>

    <li class="nav-item">
      <a href="upload.php" class="nav-link d-block px-4 py-2 rounded-0 <?= basename($_SERVER['PHP_SELF']) === 'upload.php' ? 'fw-bold bg-light border-start border-4 border-primary text-dark' : 'text-dark' ?>">
        <i class="bi bi-upload me-2"></i> Upload Data
      </a>
    </li>

    <li class="nav-item">
      <a href="clean.php" class="nav-link d-block px-4 py-2 rounded-0 <?= basename($_SERVER['PHP_SELF']) === 'clean.php' ? 'fw-bold bg-light border-start border-4 border-primary text-dark' : 'text-dark' ?>">
        <i class="bi bi-brush me-2"></i> Clean Data
      </a>
    </li>

    <li class="nav-item">
      <a href="review.php" class="nav-link d-block px-4 py-2 rounded-0 <?= basename($_SERVER['PHP_SELF']) === 'review.php' ? 'fw-bold bg-light border-start border-4 border-primary text-dark' : 'text-dark' ?>">
        <i class="bi bi-flag me-2"></i> Review Issues
      </a>
    </li>

    <!-- Section: Settings -->
    <li class="px-3 text-uppercase text-muted small fw-bold mt-4 mb-2">Settings</li>

    <li class="nav-item">
      <a href="settings.php" class="nav-link d-block px-4 py-2 rounded-0 <?= basename($_SERVER['PHP_SELF']) === 'settings.php' ? 'fw-bold bg-light border-start border-4 border-primary text-dark' : 'text-dark' ?>">
        <i class="bi bi-gear me-2"></i> System Settings
      </a>
    </li>

    <li class="nav-item">
      <a href="user_profile.php" class="nav-link d-block px-4 py-2 rounded-0 <?= basename($_SERVER['PHP_SELF']) === 'user_profile.php' ? 'fw-bold bg-light border-start border-4 border-primary text-dark' : 'text-dark' ?>">
        <i class="bi bi-person me-2"></i> My Profile
      </a>
    </li>
  </ul>
</aside>
