<!-- Modal -->
<div class="modal fade" id="modalConfirmDelete" tabindex="-1" aria-labelledby="modalConfirmDeleteLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
        <div class="modal-header">
        <h5 class="modal-title" id="modalConfirmDeleteLabel"><?= __('Potvrzení smazání') ?></h5>
        <button type="button" class="btn-sm btn-close" data-bs-dismiss="modal" aria-label="<?= __('Zavřít') ?>"></button>
        </div>
        <div class="modal-body">
        <?= __('Opravdu chcete smazat tento záznam?') ?>
        </div>
        <div class="modal-footer">
        <button type="button" class="btn btn-sm btn-secondary cancel" data-bs-dismiss="modal"><?= __('Zrušit') ?></button>
        <a href="#" class="btn btn-sm btn-danger confirm"><?= __('Smazat') ?></a>
        </div>
    </div>
    </div>
</div>