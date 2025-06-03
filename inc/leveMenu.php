<?php
$homeLink = '<a class="nav-link '. ((c_ScriptBaseName == 'index') ? 'active' : '') .'" href="'.c_MainUrl.'">'.__('Úvodní stránka').'</a>';
?>
<div class="col-2 d-none d-md-block" id="sidebar"> <!-- Skryté na mobilu, viditelné na širších obrazovkách -->
    <h6 class="offcanvas-title nav-item mb-3"><?= $homeLink ?></h6>

<div class="accordion" id="accordionMeridla">
    <?php foreach ($oOdecet->aMeridla as $id => $meridlo): 
        $isActive = ($id == $oOdecet->aMeridlo["id"]);
        $collapseId = 'collapse' . $id;
    ?>
        <div class="accordion-item">
            <h2 class="accordion-header" id="heading<?= $id ?>">
                <button class="accordion-button <?= $isActive ? '' : 'collapsed' ?>"
                        type="button"
                        data-bs-toggle="collapse"
                        data-bs-target="#<?= $collapseId ?>"
                        aria-expanded="<?= $isActive ? 'true' : 'false' ?>"
                        aria-controls="<?= $collapseId ?>">
                    <?= htmlspecialchars($meridlo["nazev"]) ?>
                </button>
            </h2>
            <div id="<?= $collapseId ?>"
                 class="accordion-collapse collapse <?= $isActive ? 'show' : '' ?>"
                 aria-labelledby="heading<?= $id ?>"
                 data-bs-parent="#accordionMeridla">
                <div class="accordion-body p-2">
                    <ul class="nav flex-column mb-3">
                        <li class="nav-item">
                            <a class="nav-link pb-1 pt-1 <?= (c_ScriptBaseName == "seznamOdectu" && $id == $oOdecet->aMeridlo["id"] ? 'active' : '') ?>"
                               href="<?= c_MainUrl . 'seznamOdectu.php?idm=' . $id . '&t=' . time() ?>">
                                <?= __('Seznam odečtů') ?>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link pb-1 pt-1 <?= (c_ScriptBaseName == "zapisOdecet" && $id == $oOdecet->aMeridlo["id"] ? 'active' : '') ?>"
                               href="<?= c_MainUrl . 'zapisOdecet.php?idm=' . $id . '&t=' . time() ?>">
                                <?= __('Nový odečet') ?>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link pb-1 pt-1 <?= (c_ScriptBaseName == "zapisMeridlo" && $id == $oOdecet->aMeridlo["id"] ? 'active' : '') ?>"
                               href="<?= c_MainUrl . 'zapisMeridlo.php?idm=' . $id . '&t=' . time() ?>">
                                <?= __('Nastavení') ?>
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>
    <h6 class="offcanvas-title nav-item mb-3"><a class="nav-link" href="<?= c_MainUrl; ?>logout.php"><?= __('Odhlásit') ?></a></h6>
</div>

<!-- Offcanvas menu pro mobilní zobrazení (hamburger) -->
<div class="offcanvas offcanvas-start d-md-none" id="leve-menu">
    <div class="offcanvas-header">
        <h5 class="offcanvas-title nav-item"><?= $homeLink ?></h5>
        <button title="Close" type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
    </div>
    <div class="offcanvas-body">
    <?php foreach ($oOdecet->aMeridla as $id => $meridlo): 
        $isActive = ($id == $oOdecet->aMeridlo["id"]);
        $collapseId = 'offcanvasCollapse' . $id;
    ?>
        <div class="mb-2">
            <div class="d-flex justify-content-between align-items-center px-2">
                <button class="btn btn-link text-start w-100 <?= $isActive ? '' : 'collapsed' ?>"
                        type="button"
                        data-bs-toggle="collapse"
                        data-bs-target="#<?= $collapseId ?>"
                        aria-expanded="<?= $isActive ? 'true' : 'false' ?>"
                        aria-controls="<?= $collapseId ?>">
                    <?= htmlspecialchars($meridlo["nazev"]) ?>
                </button>
            </div>
            <div id="<?= $collapseId ?>" class="collapse <?= $isActive ? 'show' : '' ?>" data-bs-parent=".offcanvas-body">
                <ul class="nav flex-column px-3 pb-2">
                    <li class="nav-item">
                        <a class="nav-link pb-1 pt-1 <?= (c_ScriptBaseName == "seznamOdectu" && $id == $oOdecet->aMeridlo["id"] ? 'active' : '') ?>"
                            href="<?= c_MainUrl . 'seznamOdectu.php?idm=' . $id . '&t=' . time() ?>">
                            <?= __('Seznam odečtů') ?>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link pb-1 pt-1 <?= (c_ScriptBaseName == "zapisOdecet" && $id == $oOdecet->aMeridlo["id"] ? 'active' : '') ?>"
                            href="<?= c_MainUrl . 'zapisOdecet.php?idm=' . $id . '&t=' . time() ?>">
                            <?= __('Nový odečet') ?>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link pb-1 pt-1 <?= (c_ScriptBaseName == "zapisMeridlo" && $id == $oOdecet->aMeridlo["id"] ? 'active' : '') ?>"
                            href="<?= c_MainUrl . 'zapisMeridlo.php?idm=' . $id . '&t=' . time() ?>">
                            <?= __('Nastavení') ?>
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    <?php endforeach; ?>
    </div>

    <div class="offcanvas-body">
        <h5 class="offcanvas-title nav-item"><a class="nav-link" href="<?= c_MainUrl; ?>logout.php"><?= __('Odhlásit') ?></a></h5>
    </div>
</div>
