<?php
use Latecka\Utils\utils;

header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

require_once 'autoload.php';

$oUser       = new user();
$oStatistiky = new statistiky($oUser->aUser);
$oOdecet     = $oStatistiky; // alias pro leveMenu.php

$jednotka = $oStatistiky->aMeridlo['jednotka'] ?? '';

function trendbadge(float $predchozi, float $aktualni): string {
    if ($predchozi == 0) return '<span class="text-muted">—</span>';
    $pct = (($aktualni - $predchozi) / $predchozi) * 100;
    if ($pct < -0.05) {
        return '<span class="text-success"><i class="bi bi-arrow-down-short"></i>' . abs(round($pct, 1)) . '&nbsp;%</span>';
    } elseif ($pct > 0.05) {
        return '<span class="text-danger"><i class="bi bi-arrow-up-short"></i>' . round($pct, 1) . '&nbsp;%</span>';
    }
    return '<span class="text-muted">0&nbsp;%</span>';
}
?><!DOCTYPE html>
<html lang="cs">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="<?= c_MainUrl ?>Bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?= c_MainUrl ?>Bootstrap/css/icons/bootstrap-icons.css" rel="stylesheet">
    <title><?= c_AppName . ' / ' . htmlspecialchars($oStatistiky->aMeridlo['nazev'] ?? '') ?> – <?= __('Statistiky') ?></title>
    <script src="<?= c_MainUrl ?>Bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="<?= c_MainUrl ?>inc/jquery-3.6.4.min.js"></script>
    <script src="<?= c_MainUrl ?>inc/home.js?ch=<?= md5_file('inc/home.js') ?>"></script>
    <link href="<?= c_MainUrl ?>inc/css/home.css?ch=<?= md5_file(c_FileRoot.'inc/home.css') ?>" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
  </head>
  <body class="ps-3 pe-3 m-0 border-0 bg-light">
    <?php include 'inc/navbar-top.php' ?>
    <div class="row">
      <div class="col p-3 bg-white m-2 rounded-3">
        <div class="row">
          <div class="col-2 d-print-none d-none d-md-block"></div>
          <div class="col ps-0">
            <h1 class="fs-3 ps-0">
              <?= htmlspecialchars($oStatistiky->aMeridlo['nazev'] ?? '') ?>
              – <?= __('Statistiky') ?>
            </h1>
          </div>
        </div>
        <div class="row pt-0">
          <?php include_once 'inc/leveMenu.php'; ?>
          <div class="col" style="min-height: 85vh">

            <?php if ($oStatistiky->aMeridlo['id'] == 0) : ?>
              <div class="alert alert-warning"><?= __('Vyberte měřidlo v levém menu.') ?></div>

            <?php elseif (!empty($oStatistiky->chyba)) : ?>
              <div class="alert alert-info">
                <i class="bi bi-info-circle me-1"></i><?= htmlspecialchars($oStatistiky->chyba) ?>
              </div>

            <?php else :
                $a  = $oStatistiky->aktualniObdobi;
                $p  = $oStatistiky->predchoziObdobi;
                $pc = $oStatistiky->predchoziObdobiCele;
                $pr = $oStatistiky->projekce;
                $maProjekci = $pr['dniDoKonce'] > 0;
            ?>

              <p class="text-muted small mb-3">
                <?php if ($a['dniOdhad'] > 0 || $p['dniOdhad'] > 0 || $maProjekci) : ?>
                  <i class="bi bi-asterisk text-warning-emphasis" style="font-size:.65rem"></i>
                  <?= __('Hodnoty s hvězdičkou obsahují odhad dopočítaný z průměrné denní spotřeby.') ?>
                <?php endif; ?>
              </p>

              <div class="table-responsive">
                <table class="table table-bordered align-middle">
                  <thead class="table-light">
                    <tr>
                      <th style="min-width:190px"><?= __('Metrika') ?></th>

                      <th class="text-center">
                        <?= __('Předchozí') ?><br>
                        <small class="fw-normal text-muted">
                          <?= utils::getLocaleDate($p['od']) ?> – <?= utils::getLocaleDate($p['do']) ?>
                          <?php if ($p['dniOdhad'] > 0) : ?>
                            <br><i class="bi bi-asterisk text-warning-emphasis" style="font-size:.6rem"></i>
                            <?= __('odhad posl.') ?> <?= $p['dniOdhad'] ?> <?= __('dní') ?>
                          <?php endif; ?>
                        </small>
                      </th>

                      <th class="text-center">
                        <?= __('Aktuální') ?><br>
                        <small class="fw-normal text-muted">
                          <?= utils::getLocaleDate($a['od']) ?> – <?= __('dnes') ?>
                          <?php if ($a['dniOdhad'] > 0) : ?>
                            <br><i class="bi bi-asterisk text-warning-emphasis" style="font-size:.6rem"></i>
                            <?= __('odhad posl.') ?> <?= $a['dniOdhad'] ?> <?= __('dní') ?>
                          <?php endif; ?>
                        </small>
                      </th>

                      <th class="text-center <?= $maProjekci ? 'table-warning' : 'table-secondary' ?>">
                        <?= __('Projekce') ?> <i class="bi bi-asterisk text-warning-emphasis" style="font-size:.65rem"></i><br>
                        <small class="fw-normal text-muted">
                          <?= utils::getLocaleDate($a['od']) ?> – <?= utils::getLocaleDate($pr['konecObdobi']) ?>
                          <?php if ($maProjekci) : ?>
                            <br><?= __('zbývá') ?> <?= $pr['dniDoKonce'] ?> <?= __('dní') ?>
                          <?php else : ?>
                            <br><span class="text-success"><?= __('období uplynulo') ?></span>
                          <?php endif; ?>
                        </small>
                      </th>

                      <th class="text-center">
                        <?= __('Projekce vs. předchozí') ?><br>
                        <small class="fw-normal text-muted">
                          <?= utils::getLocaleDate($pc['od']) ?> – <?= utils::getLocaleDate($pc['do']) ?>
                        </small>
                      </th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr>
                      <td><?= __('Celková spotřeba') ?></td>
                      <td class="text-end"><?= round($p['celkovaSpotreba'], 3) ?> <?= $jednotka ?></td>
                      <td class="text-end"><?= round($a['celkovaSpotreba'], 3) ?> <?= $jednotka ?></td>
                      <td class="text-end <?= $maProjekci ? 'table-warning' : 'table-secondary' ?>">
                        <?= round($pr['celkovaSpotreba'], 3) ?> <?= $jednotka ?>
                      </td>
                      <td class="text-center"><?= trendbadge($pc['celkovaSpotreba'], $pr['celkovaSpotreba']) ?></td>
                    </tr>
                    <tr>
                      <td><?= __('Průměrná spotřeba / den') ?></td>
                      <td class="text-end"><?= round($p['prumernaSpotrebaDen'], 3) ?> <?= $jednotka ?></td>
                      <td class="text-end"><?= round($a['prumernaSpotrebaDen'], 3) ?> <?= $jednotka ?></td>
                      <td class="text-end <?= $maProjekci ? 'table-warning' : 'table-secondary' ?>">
                        <?= round($pr['prumernaSpotrebaDen'], 3) ?> <?= $jednotka ?>
                      </td>
                      <td class="text-center"><?= trendbadge($pc['prumernaSpotrebaDen'], $pr['prumernaSpotrebaDen']) ?></td>
                    </tr>
                    <tr>
                      <td><?= __('Náklady') ?></td>
                      <td class="text-end"><?= round($p['celkoveNaklady'], c_RoundNaklady) ?> <?= c_Mena ?></td>
                      <td class="text-end"><?= round($a['celkoveNaklady'], c_RoundNaklady) ?> <?= c_Mena ?></td>
                      <td class="text-end <?= $maProjekci ? 'table-warning' : 'table-secondary' ?>">
                        <?= round($pr['celkoveNaklady'], c_RoundNaklady) ?> <?= c_Mena ?>
                      </td>
                      <td class="text-center"><?= trendbadge($pc['celkoveNaklady'], $pr['celkoveNaklady']) ?></td>
                    </tr>
                  </tbody>
                </table>
              </div>

            <?php if ($oStatistiky->dostupne && !empty($oStatistiky->chartData)) :
                $cd = $oStatistiky->chartData;
                $jednotka = htmlspecialchars($cd['jednotka']);
            ?>
              <div class="mt-4">
                <div class="d-flex align-items-center mb-2 gap-2">
                  <strong class="me-1"><?= __('Graf') ?>:</strong>
                  <div class="btn-group btn-group-sm" role="group">
                    <button type="button" class="btn btn-primary active" id="btnSpotreba">
                      <?= __('Spotřeba') ?> (<?= $jednotka ?>)
                    </button>
                    <button type="button" class="btn btn-outline-primary" id="btnNaklady">
                      <?= __('Náklady') ?> (<?= c_Mena ?>)
                    </button>
                  </div>
                </div>
                <canvas id="statChart" style="max-height:420px"></canvas>
              </div>

              <script>
              (function () {
                const cd = <?= json_encode([
                    'predchozi'     => $cd['predchozi'],
                    'aktualni'      => $cd['aktualni'],
                    'predchoziCele' => $cd['predchoziCele'],
                    'projekce'      => $cd['projekce'],
                ], JSON_UNESCAPED_UNICODE) ?>;

                const jednotka = <?= json_encode($jednotka) ?>;
                const mena     = <?= json_encode(c_Mena) ?>;

                const labels = {
                  predchozi:     <?= json_encode(__('Předchozí N dní')) ?>,
                  aktualni:      <?= json_encode(__('Aktuální N dní')) ?>,
                  predchoziCele: <?= json_encode(__('Celé předchozí období')) ?>,
                  projekce:      <?= json_encode(__('Projekce')) ?>,
                };

                function segmentDash(ctx, solidCount) {
                  return ctx.p1DataIndex >= solidCount ? [6, 4] : undefined;
                }

                function makeDatasets(mode) {
                  const key = mode === 'spotreba' ? 'spotreba' : 'naklady';
                  return [
                    {
                      label: labels.predchozi,
                      data: cd.predchozi[key],
                      borderColor: '#0d6efd',
                      backgroundColor: 'transparent',
                      tension: 0.2,
                      segment: { borderDash: ctx => segmentDash(ctx, cd.predchozi.solidCount) },
                    },
                    {
                      label: labels.aktualni,
                      data: cd.aktualni[key],
                      borderColor: '#fd7e14',
                      backgroundColor: 'transparent',
                      tension: 0.2,
                      segment: { borderDash: ctx => segmentDash(ctx, cd.aktualni.solidCount) },
                    },
                    {
                      label: labels.predchoziCele,
                      data: cd.predchoziCele[key],
                      borderColor: '#198754',
                      backgroundColor: 'transparent',
                      tension: 0.2,
                      segment: { borderDash: ctx => segmentDash(ctx, cd.predchoziCele.solidCount) },
                    },
                    {
                      label: labels.projekce,
                      data: cd.projekce[key],
                      borderColor: '#dc3545',
                      backgroundColor: 'transparent',
                      borderDash: [6, 4],
                      tension: 0,
                    },
                  ];
                }

                const ctx = document.getElementById('statChart').getContext('2d');
                let mode = 'spotreba';

                const chart = new Chart(ctx, {
                  type: 'line',
                  data: { datasets: makeDatasets(mode) },
                  options: {
                    parsing: false,
                    animation: false,
                    scales: {
                      x: {
                        type: 'linear',
                        title: { display: true, text: <?= json_encode(__('Měsíce od začátku období')) ?> },
                        ticks: {
                          callback: val => (val % 30 === 0 || val === 0) ? Math.round(val / 30) : null,
                          stepSize: 30,
                        },
                      },
                      y: {
                        title: { display: true, text: jednotka },
                        beginAtZero: true,
                      },
                    },
                    plugins: {
                      legend: { position: 'bottom' },
                      tooltip: {
                        callbacks: {
                          label: ctx => ctx.dataset.label + ': ' + ctx.parsed.y,
                        }
                      }
                    },
                    elements: { point: { radius: 2 } },
                  },
                });

                function switchMode(newMode) {
                  mode = newMode;
                  chart.data.datasets = makeDatasets(mode);
                  chart.options.scales.y.title.text = mode === 'spotreba' ? jednotka : mena;
                  chart.update();
                  document.getElementById('btnSpotreba').classList.toggle('active', mode === 'spotreba');
                  document.getElementById('btnSpotreba').classList.toggle('btn-primary', mode === 'spotreba');
                  document.getElementById('btnSpotreba').classList.toggle('btn-outline-primary', mode !== 'spotreba');
                  document.getElementById('btnNaklady').classList.toggle('active', mode === 'naklady');
                  document.getElementById('btnNaklady').classList.toggle('btn-primary', mode === 'naklady');
                  document.getElementById('btnNaklady').classList.toggle('btn-outline-primary', mode !== 'naklady');
                }

                document.getElementById('btnSpotreba').addEventListener('click', () => switchMode('spotreba'));
                document.getElementById('btnNaklady').addEventListener('click', () => switchMode('naklady'));
              })();
              </script>
            <?php endif; ?>

            <?php endif; ?>
          </div>
        </div>
      </div>
    </div>
    <script src="<?= c_MainUrl ?>inc/validation-form.js"></script>
  </body>
</html>
