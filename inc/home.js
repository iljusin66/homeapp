const smazOdecet = function (el) {
    spinnerButtonOn(el);

    var modalWindow = $('#modalInfo');
    var myModal = new bootstrap.Modal(modalWindow, {
        keyboard: false,
        backdrop: true,
        focus: true
    });


    $.ajax({
        url: _hostname + 'ajaxSmazOdecet.php'
        , type: 'GET'
        , dataType: "json"
        , error: function (jqXHR, textStatus, errorThrown) {
            $('.modal-title', modalWindow).text('Chyba požadavku!!');
            $('.modal-body', modalWindow).html('<p class="text-danger-emphasis">Chyba #AJAX004 při zpracování požadavku!</p><br><p>Text chyby: ' + textStatus + '</p>\n\
<br><p>jqXHR:<pre>' + JSON.stringify(jqXHR) + '</pre></p><br><p>errorThrown:<pre>' + JSON.stringify(errorThrown) + '</pre></p>');
            myModal.show();
            spinnerButtonOff(el);
        }
        , success: function (data) {
            if (data.status === "ok") {
                $('.modal-title', modalWindow).text(data.modalTitle);
                $('.modal-body', modalWindow).html(data.modalBody);
            } else {
                $('.modal-title', modalWindow).text('Chyba importu mezd!!');
                $('.modal-body', modalWindow).html('<p class="text-danger-emphasis">Chyba #AJAX003 při zpracování importu mezd!</p><br><p>Navrácená data:<pre>' + JSON.stringify(data) + '</pre></p>');

            }
            ;
            myModal.show();
            spinnerButtonOff(el);
        }
    });
};

$document.ready(function () {

    $('.smazatOdecet').on('click', function (e) {
        e.preventDefault();
        smazOdecet(this);
    });


});