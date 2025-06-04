const _hostname = window.location.protocol + '//' + window.location.hostname + '/';

const smazOdecet = function (el) {
    spinnerButtonOn(el);

    var modalWindow = $('#modalConfirmDelete');
    var myModal = new bootstrap.Modal(modalWindow, {
        keyboard: false,
        backdrop: true,
        focus: true
    });

    myModal.show();
    spinnerButtonOff(el);
    var url = el.attr('data-url');
    
    //Akce po potvrzení. Přesměruju na smazani
    modalWindow.find('.confirm').off('click').on('click', function() {
        window.location.href=url;
    });
    
    //Akce po zrušení. Zavřu modal
    modalWindow.find('.cancel').off('click').on('click', function() {
        spinnerButtonOff(el);
        myModal.hide();
    });

};

/*
const smazOdecetAjax = function (ido, idm, el) {
    $.ajax({
        url: _hostname + 'ajaxSmazOdecet.php?ido=' + ido + '&idm=' + idm
        , type: 'GET'
        , dataType: "json"
        , error: function (jqXHR, textStatus, errorThrown) {
            $('.modal-title', modalWindow).text('Chyba volání vzdáleného požadavku!');
            $('.modal-body', modalWindow).html('<p class="text-danger-emphasis">Chyba #AJAX004 při zpracování požadavku!</p><br><p>Text chyby: ' + textStatus + '</p>\n\
<br><p>jqXHR:<pre>' + JSON.stringify(jqXHR) + '</pre></p><br><p>errorThrown:<pre>' + JSON.stringify(errorThrown) + '</pre></p>');
            spinnerButtonOff(el);
        }
        , success: function (data) {
            if (data.status === "success") {
                $('.modal-title', modalWindow).text(data.modalTitle);
                $('.modal-body', modalWindow).html(data.modalBody);
                myModal.hide();
            } else {
                $('.modal-title', modalWindow).text('Chyba v odpovědi požadavku!');
                $('.modal-body', modalWindow).html('<p class="text-danger-emphasis">Chyba #AJAX003 při zpracování importu mezd!</p><br><p>Navrácená data:<pre>' + JSON.stringify(data) + '</pre></p>');

            }
            ;

            spinnerButtonOff(el);
        }
    });
}
*/
const spinnerButtonOn = function (el, spinnerText = ' strpení prosím...', disable = true) {
    el.attr('data-text-orig', el.html());
    var spinner = '<span class="spinner-border spinner-border-sm"></span>';
    el.html(spinner + spinnerText);
    if (disable) {
        el.attr('disabled', 'disabled');
}
};

const spinnerButtonOff = function (el) {
    el.html(el.attr('data-text-orig'));
    el.removeAttr('disabled');
    el.removeAttr('data-text-orig');
};



// Zpracování události po načtení DOM
$(document).ready(function () {
    $('.smazatOdecet').on('click', function (e) {
        e.preventDefault();
        smazOdecet($(this));
    });

});