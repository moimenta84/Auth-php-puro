<?php

use App\Core\Auth\Auth; ?>

<header class="bg-dark mb-3">
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark px-3">
        <a class="navbar-brand fw-bold text-white" href="<?= HOME_URL ?>">
            Tienda
        </a>

        <div class="ms-auto d-flex align-items-center">

            <?php if (Auth::check()): ?>
                <span class="me-3 text-white">
                    😁 <?= Auth::user()->nombre; ?>
                </span>

                <form action="<?= url('/logout.php') ?>" method="POST" class="d-inline">
                    <button type="submit" class="btn btn-outline-light btn-sm">
                        Cerrar sesión
                    </button>
                </form>

            <?php else: ?>
                <a class="btn btn-light btn-sm"
                    href="<?= url('/login.php') ?>">
                    Iniciar sesión
                </a>
            <?php endif; ?>

        </div>
    </nav>
</header>