<?php
use Latecka\Utils\request;
$status = request::string('status', 'GET');
$message = '';
$alertType = '';
if ($status === 'success') {
    if (c_ScriptBaseName == 'zapisOdecet' || c_ScriptBaseName == 'seznamOdectu') {
        $message = __('Odečet byl úspěšně uložen.');
    } else {
        $message = __('Záznam byl úspěšně uložen.');
    }
    $message = __('Odečet byl úspěšně uložen.');
    $alertType = 'alert-success';
} elseif ($status === 'error') {
    if (c_ScriptBaseName == 'zapisOdecet' || c_ScriptBaseName == 'seznamOdectu') {
        $message = __('Chyba při ukládání odečtu');
    } else {
        $message = __('Záznam se nepodařilo uložit.');
    }
    $alertType = 'alert-danger';
}

if ($message) : ?>
    <div id="statusAlert" class="alert <?= $alertType ?> alert-dismissible fade show position-fixed top-0 start-50 translate-middle-x mt-3 shadow" role="alert" style="z-index: 1055; min-width: 300px; max-width: 90vw;">
        <?= $message ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="<?= __('Zavřít') ?>"></button>
    </div>
    <script>
        setTimeout(function() {
            var alert = document.getElementById('statusAlert');
            if (alert) {
                var bsAlert = bootstrap.Alert.getOrCreateInstance(alert);
                bsAlert.close();
            }
        }, 3000);
    </script>
<?php endif; ?>