<?php
USE Latecka\Utils\utils;

require_once 'autoload.php';

$oUser = new user();
$oZarizeni = new zarizeni();
$oOdpocet = new vlozitOdpocet();
?><!DOCTYPE html>
<html lang="cs">
    <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="<?= c_MainUrl; ?>Bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <!-- https://icons.getbootstrap.com/ -->
    <link href="<?= c_MainUrl; ?>Bootstrap/css/icons/bootstrap-icons.css" rel="stylesheet">
    <title><?= __('Nový odpočet') ?></title>
    <script src="<?= c_MainUrl; ?>inc/jquery-3.6.4.min.js"></script>
    <script src="<?= c_MainUrl; ?>Bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="<?= c_MainUrl; ?>inc/home.js?ch=<?= md5_file('inc/home.js') ?>"></script>
    <style>
        .tile { background-color: #d2f4ea; border:white solid 0.15em; border-radius: 0.5em; padding:1em; }
        .tile:hover { background-color: #a6e9d5; }
        h2 { font-size:1.1em; min-height:3.6em; }
        p { min-height:3em; display: inline-block; }
    </style>
  </head>
  <body class="ps-3 pe-3 m-0 border-0 bg-light">
        <?php include('inc/navbar-top.php') ?>
        <div class="row">
            <div class="col p-3 bg-white m-2 rounded-3">
                <div class="row">
                    <div class="col-2 d-print-none"></div>
                    <div class="col ps-0">
                        <h1 class="fs-3 ps-0"><?php
                        if ($oZam->idzamestnance > 0) :
                            echo $oZam->aZamestnanci[$oZam->idzamestnance]['jmeno'];
                        else:
                            echo 'Nový zaměstnanec';
                        endif; ?>
                        </h1>
                    </div>
                </div>
                <div class="row pt-0">
                    <?php include_once 'inc/leveMenu.php'; ?>
                    <div class="col" style="min-height: 85vh">
                        <div id="dataContainer" class="row">
                            <form class="p-1 pt-0 me-1 col-l-10 col-12" id="frmZamestnanecEdit" action="zamestnanecEdit.php" method="POST" novalidate>
                                <input type="hidden" name="idzamestnance" value="<?= $oZam->idzamestnance ?>">
                                <input type="hidden" name="ulozZamestnance" value="1">
                                <fieldset class="row">
                                    <legend class="form-label">Základní nastavení</legend>
                                    <div class="col-4">
                                        <div class="input-group input-group-sm mb-1 ">
                                            <label class="input-group-text col-4" id="label_jmeno" for="jmeno" style="font-size: .875rem;">Jméno</label>
                                            <input type="text" data-kontrolaZmeny name="jmeno" id="jmeno" value="<?= utils::safeForm($oZam->aZamestnanci[$oZam->idzamestnance]['jmeno']) ?>" class="form-control">
                                            <div class="invalid-feedback w-100"><div class="col col-4">sss</div><div class="col col-8">zadej jméno zaměstnance</div></div>
                                        </div>
                                    </div>

                                    <div class="col-4">
                                        <div class="input-group input-group-sm mb-1 ">
                                            <label class="input-group-text col-4" for="odbornost" id="label_odbornost" style="font-size: .875rem;">Odbornost</label>
                                            <input type="text" data-kontrolaZmeny name="odbornost" id="odbornost" value="<?= utils::safeForm($oZam->aZamestnanci[$oZam->idzamestnance]['odbornost']) ?>" class="form-control">
                                        </div>
                                    </div>

                                    <div class="col-4">
                                        <div class="input-group input-group-sm mb-1 ">
                                            <label class="input-group-text col-4" for="osloveni" id="label_osloveni" style="font-size: .875rem;">Oslovení</label>
                                            <input type="text" data-kontrolaZmeny name="osloveni" id="osloveni" value="<?= utils::safeForm($oZam->aZamestnanci[$oZam->idzamestnance]['osloveni']) ?>" class="form-control">
                                        </div>
                                    </div>
                                    
                                    
                                    <div class="col-4">
                                        <div class="input-group input-group-sm mb-1">
                                            <label class="input-group-text col-4" id="label_idlekare_cl" for="idlekare_cl" style="font-size: .875rem;">Jméno v CL</label>
                                            <select data-kontrolaZmeny name="idlekare_cl" id="idlekare_cl" class="form-select">
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-4">
                                        <div class="input-group input-group-sm mb-1">
                                            <label class="input-group-text col-4" id="label_idlekare_cp" for="idlekare_cp" style="font-size: .875rem;">Jméno v CP</label>
                                            <select data-kontrolaZmeny name="idlekare_cp" id="idlekare_cp" class="form-select">
                                            </select>
                                        </div>
                                    </div>

                                    
                                    <div class="col-4">
                                        <div class="input-group input-group-sm mb-1">
                                            <!--
                                            <label class="input-group-text col-4" id="label_idzamestnance_dochazka" for="idzamestnance_dochazka" style="font-size: .875rem;">Jméno v docházce</label>
                                            <select data-kontrolaZmeny name="idzamestnance_dochazka" id="idzamestnance_dochazka" class="form-select col-7">
                                                
                                            </select>
                                            -->
                                        </div>
                                    </div>
                                    
                                    <div class="col-4">
                                        <div class="input-group input-group-sm mb-1 ">
                                            <label class="input-group-text col-4" id="label_email" for="email" style="font-size: .875rem;">E-mail soukr. (mzdy)</label>
                                            <input type="email" name="email" id="email" value="<?= utils::safeForm($oZam->aZamestnanci[$oZam->idzamestnance]['email']) ?>" class="form-control">
                                        </div>
                                    </div>

                                    <div class="col-4">
                                        <div class="input-group input-group-sm mb-1 ">
                                            <label class="input-group-text col-4" id="label_email_f" for="email_f" style="font-size: .875rem;">E-mail firemní</label>
                                            <input type="email" name="email_f" id="email_f" value="<?= utils::safeForm($oZam->aZamestnanci[$oZam->idzamestnance]['email_f']) ?>" class="form-control">
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="input-group input-group-sm mb-1 ">
                                            <label class="input-group-text col-1" for="poznamka" id="label_poznamka" style="font-size: .875rem; min-width:8.5vw;">Poznámka</label>
                                            <input type="text" data-kontrolaZmeny name="poznamka" id="poznamka" value="<?= utils::safeForm($oZam->aZamestnanci[$oZam->idzamestnance]['poznamka']) ?>" class="form-control">
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="input-group input-group-sm ">
                                            <label class="input-group-text col-1" for="pocitane_vykony" id="label_pocitane_vykony" style="font-size: .875rem; min-width:8.5vw;">Počítané výkony</label>
                                            <input type="text" data-kontrolaZmeny name="pocitane_vykony" id="pocitane_vykony" value="<?= utils::safeForm($oZam->aZamestnanci[$oZam->idzamestnance]['pocitane_vykony']) ?>" class="form-control">
                                        </div>
                                    </div>
                                </fieldset>
                                <fieldset class="row mt-3">
                                    <legend class="form-label">Certifikát & eRecept</legend>
                                    <div class="col-3">
                                        <div class="input-group input-group-sm mb-1">
                                            <label class="input-group-text col-7" id="label_platnost_certifikatu_do" for="platnost_certifikatu_do" style="font-size: .875rem;">Certifikát platný do</label>
                                            <input type="date" class="form-control form-control-sm" id="platnost_certifikatu_do" name="platnost_certifikatu_do" value="<?= utils::safeForm($oZam->aZamestnanci[$oZam->idzamestnance]["platnost_certifikatu_do"]) ?>">
                                        </div>
                                    </div>
                                    <div class="col-9"></div>

                                    <div class="col-3">
                                        <div class="input-group input-group-sm mb-1">
                                            <label class="input-group-text col-7" id="label_platnost_heslo_sukl_do" for="platnost_heslo_sukl_do" style="font-size: .875rem;">Heslo SUKL platné do</label>
                                            <input type="date" class="form-control form-control-sm" id="platnost_heslo_sukl_do" name="platnost_heslo_sukl_do" value="<?= utils::safeForm($oZam->aZamestnanci[$oZam->idzamestnance]["platnost_heslo_sukl_do"]) ?>">
                                        </div>
                                    </div>
                                    <div class="col-3">
                                        <div class="input-group input-group-sm mb-1 ">
                                            <label class="input-group-text col-4" for="sukl_heslo" id="label_sukl_heslo" style="font-size: .875rem;">Heslo SUKL</label>
                                            <input type="text" data-kontrolaZmeny name="sukl_heslo" id="sukl_heslo" value="<?= utils::safeForm($oZam->aZamestnanci[$oZam->idzamestnance]['sukl_heslo']) ?>" class="form-control">
                                        </div>
                                    </div>
                                    <div class="col-5">
                                        <div class="input-group input-group-sm mb-1 ">
                                            <label class="input-group-text col-2" for="sukl_uuid" id="label_sukl_uuid" style="font-size: .875rem;">UUID SUKL</label>
                                            <input type="text" data-kontrolaZmeny name="sukl_uuid" id="sukl_uuid" value="<?= utils::safeForm($oZam->aZamestnanci[$oZam->idzamestnance]['sukl_uuid']) ?>" class="form-control">
                                        </div>
                                    </div>
                                    <div class="col">
                                        <a href="#" style="display: none;" target="_blank" id="linkToPortalSUKL">Portál SUKL</a>
                                    </div>
                                </fieldset>
                                <fieldset class="row mt-3">
                                    <legend class="form-label">Nastavení odměn</legend>
                                    <div class="col-4">
                                        <div class="input-group input-group-sm mb-1 ">
                                            <label class="input-group-text col-4" id="label_smluvnimzda" for="smluvnimzda" style="font-size: .875rem;">Smluvní mzda</label>
                                            <select data-kontrolaZmeny name="smluvnimzda_firma" id="smluvnimzda_firma" class="form-select">
                                                <option value="">---</option>
                                            </select>
                                            <input type="text" data-kontrolaZmeny name="smluvnimzda" id="smluvnimzda" value="<?= utils::fixInt($oZam->aConfigZamestnanec["smluvnimzda"]) ?>" class="form-control text-end">
                                        </div>
                                    </div>
                                    <div class="col-4">
                                        <div class="input-group input-group-sm mb-1 ">
                                            <label class="input-group-text col-4" id="label_pausal" for="pausal" style="font-size: .875rem;">Paušál</label>
                                            <input type="text" data-kontrolaZmeny name="pausal" id="pausal" value="<?= utils::fixInt($oZam->aConfigZamestnanec["pausal"]) ?>" class="form-control text-end">
                                        </div>
                                    </div>
                                    <div class="col-4">
                                        <div class="input-group input-group-sm mb-1 ">
                                            <label class="input-group-text col-4" for="hodinovamzda" id="label_hodinovamzda" style="font-size: .875rem;">Hodinová mzda</label>
                                            <input type="text" data-kontrolaZmeny name="hodinovamzda" id="hodinovamzda" value="<?= utils::fixInt($oZam->aConfigZamestnanec["hodinovamzda"]) ?>" class="form-control text-end">
                                        </div>
                                    </div>
                                    <div class="col-4">
                                        <div class="form-check form-switch mt-1 ms-1">
                                            <label class="form-check-label" for="b_premiemzda" style="font-size: .875rem;">Prémie do mzdy?</label>
                                            <input class="form-check-input" type="checkbox" data-kontrolaZmeny id="b_premiemzda" name="b_premiemzda" value="1" <?= ($oZam->aConfigZamestnanec["b_premiemzda"] == 1) ? 'checked' : '' ?>>
                                        </div>
                                    </div>
                                    <div class="col-4">
                                        <div class="form-check form-switch mt-1 ms-1">
                                            <label class="form-check-label" for="b_ico" style="font-size: .875rem;">Může zaměstnanec fakturovat?</label>
                                            <input  data-bs-toggle="collapse" data-bs-target="#collapseFaktury" class="form-check-input" type="checkbox" data-kontrolaZmeny id="b_ico" name="b_ico" value="1" <?= ($oZam->aConfigZamestnanec["b_ico"] == 1) ? 'checked' : '' ?>>
                                        </div>
                                    </div>
                                    <div class="col-4"></div>
                                </fieldset>
                                <fieldset class="row mt-3 collapse <?= ($oZam->aConfigZamestnanec["b_ico"] == 1) ? 'show' : '' ?>" id="collapseFaktury">
                                    <div class="col-4"></div>
                                     <div class="col-4">
                                        <div class="input-group input-group-sm mb-1 ">
                                            <label class="input-group-text col-4" for="zadavatel_fakturace" id="label_zadavatel_fakturace" style="font-size: .875rem;">Faktury vystavit:</label>
                                            <select data-kontrolaZmeny name="zadavatel_fakturace" id="zadavatel_fakturace" class="form-select">
                                                <option value="">---</option>
                                            </select>
                                        </div>
                                    </div>
                                </fieldset>
                                <fieldset class="row mt-3">
                                    <legend class="form-label">Kvartální odměny za vedení ordinace</legend>
                                    <?php for ($i=1; $i<=3; $i++) : ?>
                                    <div class="col-4">
                                        <div class="input-group input-group-sm mb-1 ">
                                            <label class="input-group-text col-4" id="label_vedeniordinace_icp_<?= $i ?>" for="vedeniordinace_icp_<?= $i ?>" style="font-size: .875rem;">Vedení ordinace</label>
                                            <select data-kontrolaZmeny name="vedeniordinace_icp_<?= $i ?>" id="vedeniordinace_icp_<?= $i ?>" class="form-select">
                                                <option value="">---</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-8">
                                        <div class="input-group input-group-sm mb-1 ">
                                            <label class="input-group-text col-2" for="vedeniordinace_mimovykony_<?= $i ?>" id="label_vedeniordinace_mimovykony_<?= $i ?>" style="font-size: .875rem;">Vyjmuté výkony</label>
                                            <input type="text" placeholder='nepočítané výkony (oddělené čárkou)' data-kontrolaZmeny name="vedeniordinace_mimovykony_<?= $i ?>" id="vedeniordinace_mimovykony_<?= $i ?>" value="<?= utils::safeForm($oZam->aConfigZamestnanec["vedeniordinace_mimovykony_".$i]) ?>" class="form-control">
                                        </div>
                                    </div>
                                    <?php endfor ?>
                                </fieldset>
                                <fieldset class="row mt-3">
                                    <legend class="form-label">Clinterap</legend>
                                    <div class="col-4">
                                        <div class="input-group input-group-sm mb-1">
                                            <label class="input-group-text col-4" id="label_uvazek" for="uvazek" style="font-size: .875rem;">Úvazek</label>
                                            <input type="text" data-kontrolaZmeny name="uvazek" id="uvazek" value="<?= utils::fixFloat($oZam->aConfigZamestnanec["uvazek"]) ?>" class="form-control text-end">
                                        </div>
                                    </div>
                                    <div class="col-4">
                                        <div class="input-group input-group-sm mb-1">
                                            <label class="input-group-text col-4" id="label_proc_hodnotayvykonu_ord" for="proc_hodnotayvykonu_ord" style="font-size: .875rem;">Koef. odměny ORD</label>
                                            <input type="text" data-kontrolaZmeny name="proc_hodnotayvykonu_ord" id="proc_hodnotayvykonu_ord" value="<?= utils::fixFloat($oZam->aConfigZamestnanec["proc_hodnotayvykonu_ord"]) ?>" class="form-control text-end">
                                        </div>
                                    </div>
                                    <div class="col-4">
                                        <div class="input-group input-group-sm mb-1">
                                            <label class="input-group-text col-4" id="label_proc_hodnotayvykonu_ost" for="proc_hodnotayvykonu_ost" style="font-size: .875rem;">Koef. odměny OST</label>
                                            <input type="text" data-kontrolaZmeny name="proc_hodnotayvykonu_ost" id="proc_hodnotayvykonu_ost" value="<?= utils::fixFloat($oZam->aConfigZamestnanec["proc_hodnotayvykonu_ost"]) ?>" class="form-control text-end">
                                        </div>
                                    </div>
                                </fieldset>
                                <fieldset class="row mt-3">
                                    <legend class="form-label">Clintrial</legend>
                                    <div class="col-4">
                                        <div class="input-group input-group-sm mb-1">
                                            <label class="input-group-text col-4" id="label_proc_hodnotayvykonu_cli" for="proc_hodnotayvykonu_cli" style="font-size: .875rem;">Koef. odměny CLI</label>
                                            <input type="text" data-kontrolaZmeny name="proc_hodnotayvykonu_cli" id="proc_hodnotayvykonu_cli" value="<?= utils::fixFloat($oZam->aConfigZamestnanec["proc_hodnotayvykonu_cli"]) ?>" class="form-control text-end">
                                        </div>
                                    </div>
                                    <div class="col-4">
                                        
                                    </div>
                                    <div class="col-4">
                                        
                                    </div>
                                </fieldset>
                                
                                <fieldset class="row mt-3">
                                    <legend class="form-label">Dohody</legend>
                                    <?php for($i=1;$i<=3;$i++) : ?>
                                    <div class="col-4">
                                        <div class="input-group input-group-sm mb-1">
                                            <label class="input-group-text col-4" id="label_firma_dohoda<?= $i ?>" for="firma_dohoda<?= $i ?>" style="font-size: .875rem;">Dohoda č. <?= $i ?></label>
                                            <select data-kontrolaZmeny name="firma_dohoda<?= $i ?>" id="firma_dohoda<?= $i ?>" class="form-select">
                                                <option value="">---</option>
                                                <option value="Clinterap" <?= ($oZam->aConfigZamestnanec["firma_dohoda".$i] == 'Clinterap') ? 'selected' : ''?>>Clinterap</option>
                                                <option value="Clintrial" <?= ($oZam->aConfigZamestnanec["firma_dohoda".$i] == 'Clintrial') ? 'selected' : ''?>>Clintrial</option>
                                                <option value="Euromed" <?= ($oZam->aConfigZamestnanec["firma_dohoda".$i] == 'Euromed') ? 'selected' : ''?>>Euromed</option>
                                            </select>
                                        </div>
                                    </div>
                                    <?php endfor; ?>
                                    <div class="col-4">
                                        <div class="form-check form-switch mt-1 ms-1">
                                            <label class="form-check-label" for="b_vlastnizdanenidohody" style="font-size: .875rem;">Daní si zaměstnanec první dohodu sám?</label>
                                            <input class="form-check-input" type="checkbox" data-kontrolaZmeny id="b_vlastnizdanenidohody" name="b_vlastnizdanenidohody" value="1" <?= ($oZam->aConfigZamestnanec["b_vlastnizdanenidohody"] == 1) ? 'checked' : '' ?>>
                                        </div>
                                    </div>
                                    <div class="col-4">
                                        
                                    </div>
                                    <div class="col-4">
                                        
                                    </div>
                                </fieldset>
                                <fieldset class="row mt-3">
                                    <legend class="form-label">Pravidelný odpočet</legend>
                                    <div class="col-3">
                                        <div class="input-group input-group-sm mb-1 ">
                                            <label class="input-group-text col-5" for="odpocet" id="label_odpocet" style="font-size: .875rem;">Odpočet</label>
                                            <input type="text" data-kontrolaZmeny name="odpocet" id="odpocet" value="<?= utils::fixInt($oZam->aConfigZamestnanec["odpocet"]) ?>" class="form-control text-end">
                                        </div>                                        
                                    </div>
                                    <div class="col-3">
                                        <div class="input-group input-group-sm mb-1 ">
                                            <label class="input-group-text col-5" id="label_odpocet_datum_do" for="odpocet_datum_do" style="font-size: .875rem;">Platné do</label>
                                            <input type="date" class="form-control form-control-sm " id="odpocet_datum_do" name="odpocet_datum_do" value="<?= utils::safeForm($oZam->aConfigZamestnanec["odpocet_datum_do"]) ?>">
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="input-group input-group-sm mb-1 ">
                                            <label class="input-group-text col-2" id="label_odpocet_popis" for="odpocet_popis" style="font-size: .875rem;">Poznámka</label>
                                            <input type="text" data-kontrolaZmeny class="form-control form-control-sm" id="odpocet_popis" name="odpocet_popis" value="<?= utils::safeForm($oZam->aConfigZamestnanec["odpocet_popis"]) ?>">
                                        </div>
                                    </div>
                                    
                                </fieldset>
                                <fieldset class="row mt-3">
                                    <legend class="form-label">Pravidelné příplatky</legend>
                                    <?php foreach ((array) $oZam->aCiselnikPriplatku AS $id_cis_priplatu => $nazevpriplatku) : ?>
                                    <div class="col-3">
                                        <div class="input-group input-group-sm mb-1 ">
                                            <label class="input-group-text col-5" id="label_priplatek_<?= $id_cis_priplatu ?>" for="priplatek_<?= $id_cis_priplatu ?>" style="font-size: .875rem;"><?= $nazevpriplatku ?></label>
                                            <input type="text" data-kontrolaZmeny name="priplatek[<?= $id_cis_priplatu ?>]" id="priplatek_<?= $id_cis_priplatu ?>" value="<?= utils::fixInt($oZam->aConfigZamestnanec["priplatek"][$id_cis_priplatu]) ?>" class="form-control text-end">
                                        </div>
                                    </div>
                                    <div class="col-3">
                                        <div class="input-group input-group-sm">
                                            <label class="input-group-text col-5" id="label_priplatek_datum_do[<?= $id_cis_priplatu ?>]" for="priplatek_datum_do[<?= $id_cis_priplatu ?>]" style="font-size: .875rem;">Platné do</label>
                                            <input type="date" class="form-control form-control-sm " id="priplatek_datum_do[<?= $id_cis_priplatu ?>]" name="priplatek_datum_do[<?= $id_cis_priplatu ?>]" value="<?= utils::safeForm($oZam->aConfigZamestnanec["priplatek_datum_do"][$id_cis_priplatu]) ?>">
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="input-group input-group-sm">
                                            <label class="input-group-text col-2" id="label_priplatek_popis[<?= $id_cis_priplatu ?>]" for="priplatek_popis[<?= $id_cis_priplatu ?>]" style="font-size: .875rem;">Poznámka</label>
                                            <input type="text" data-kontrolaZmeny class="form-control form-control-sm " id="priplatek_popis[<?= $id_cis_priplatu ?>]" name="priplatek_popis[<?= $id_cis_priplatu ?>]" value="<?= utils::safeForm($oZam->aConfigZamestnanec["priplatek_popis"][$id_cis_priplatu]) ?>">
                                        </div>
                                    </div>
                                    
                                    <?php endforeach; ?>
                                </fieldset>
                                
                                <fieldset class="row mt-3">
                                    <legend class="form-label">Zaměstnanecký bank</legend>
                                    <div class="col-3">
                                        <div class="input-group input-group-sm mb-1 ">
                                            <label class="input-group-text col-5" for="banka_pocatecni_stav" id="label_banka_pocatecni_stav" style="font-size: .875rem;">Počáteční stav banku</label>
                                            <input type="text" data-kontrolaZmeny name="banka_pocatecni_stav" id="banka_pocatecni_stav" value="<?= utils::fixInt($oOdm->pocatecniStavBanku()) ?>" class="form-control text-end">
                                        </div>                                        
                                    </div>                                    
                                </fieldset>
                                <input type="submit" name="ulozit" value="Uložit" class="btn btn-primary">
                            </form>
                        </div>
                    </div>

                </div>

            </div>
        </div>
        <?php include_once 'inc/modalInfo.php'; ?>
        <script src="<?= c_MainUrl; ?>inc/validation-form.js"></script>
    </body>
</html>
