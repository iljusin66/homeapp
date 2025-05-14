
<style>body { min-height: 85vh; padding-top: 3rem !important; }</style>
<!-- Navigace s tlačítkem pro otevření menu na mobilu -->
<nav class="navbar navbar-expand-sm bg-primary fixed-top pe-4 d-print-none">
    <?php
    if (!in_array(c_ScriptBaseName, [ "dashboard", "index"])) :
    ?>
    <button class="navbar-toggler text-white" type="button" data-bs-toggle="offcanvas" data-bs-target="#leve-menu">
        <i class="bi bi-list"></i>
    </button>
    <?php endif; ?>
    <ul class="navbar-nav me-auto">
        <li class="nav-item active">
            <a href="/" class="text-white ps-4 text-decoration-none"><i class="bi-house me-1"></i> Home</a>
        </li>
    </ul>
    <ul class="navbar-nav">
        <li class="nav-item">
            <a href="logout.php" class="text-white ps-4 text-decoration-none"><i class="bi-person-x me-1"></i> Logout</a>
        </li>
    </ul>
</nav>
