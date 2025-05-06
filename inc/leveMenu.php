<div class="col-2 d-print-none" id="leve-menu">
    <div class="row pe-2">
        <ul class="nav flex-column">
            <li class="nav-item"><a class="nav-link" href="ciselnikZamestnancu.php">Seznam zaměstnanců</a></li>
        </ul>

        <h6>Nadpis</h6>
        <ul class="nav flex-column mb-5">
            <li class="nav-item"><a class="nav-link <?= (c_ScriptBaseName == 'zamestnanecEdit') ? 'active' : ''?>" href="zamestnanecEdit.php?idzamestnance=<?= $oZam->idzamestnance ?>">Konfigurace</a></li>
            <li class="nav-item"><a class="nav-link <?= (c_ScriptBaseName == 'vypocetOdmen') ? 'active' : ''?>" href="vypocetOdmen.php?idzamestnance=<?= $oZam->idzamestnance ?>">Výpočet odměny</a></li>
            <li class="nav-item"><a class="nav-link <?= (c_ScriptBaseName == 'zamestnanecOdmena') ? 'active' : ''?>" href="zamestnanecOdmena.php?idzamestnance=<?= $oZam->idzamestnance ?>&last=1">Poslední odměna</a></li>
            <li class="nav-item"><a class="nav-link <?= (c_ScriptBaseName == 'zamestnanecOdmeny') ? 'active' : ''?>" href="zamestnanecOdmeny.php?idzamestnance=<?= $oZam->idzamestnance ?>">Přehled odměn</a></li>
        </ul>
        <ul class="nav flex-column">
            <li class="nav-item"><a class="nav-link <?= (c_ScriptBaseName == 'exportOdmen') ? 'active' : ''?>" href="exportOdmen.php">Přehled odměn</a></li>
        </ul>
    </div>
</div>
