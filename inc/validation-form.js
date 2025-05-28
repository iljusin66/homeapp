document.addEventListener("DOMContentLoaded", function () {
    new FormValidace(); // Třída má být s velkým písmenem
});

class FormValidace {
    constructor() {
        console.log('Spouštím validaci formuláře');
        this.init();
    }

    init() {
        console.log('Inicializuji validaci formuláře');
        // Musíme zachytit this třídy, jinak bude this uvnitř callbacku odkazovat na DOM prvek (form)
        $('form').on('submit', (event) => {
            // this = instance třídy FormValidace
            const form = event.currentTarget;

            $('input', form).removeClass('is-invalid');

            this.validace(event, form); // předáme form, který validujeme

            // Kontrola, zda existují nevalidní inputy
            if ($(form).find('.is-invalid').length === 0) {
                form.submit(); // ručně odešleme formulář
            }
        });
    }

    validace(event, form) {
        event.preventDefault();
        console.log('Spouštím validaci formuláře');
        this.validaceDataPatternForm(form);
        this.validaceRequiredForm(form);
    }

    validaceRequiredForm(form) {
        // Projdu všechny vstupy s atributem required v rámci daného formuláře
        $('input[data-required]', form).each((index, element) => {
            this.validateRequiredElement(element);
        });
    }

    validateRequiredElement(element) {
        console.log('Validuji povinný prvek: ' + element.name);
        let chyba = $(element).attr('title') || 'Toto pole je povinné.';
        if (element.value.trim() === '') {
            console.error(element.name, chyba);
            $(element).addClass('is-invalid');
        }else{
            $(element).addClass('is-valid');
        }
    }

    validaceMinLength(length, minLength) {
        console.log('Spouštím validaci min-length: ' + minLength);
        return (length >= minLength);
    }

    validaceDataPatternForm(form) {
        console.log('Spouštím validaci data-pattern');

        $('input[data-pattern]', form).each((index, element) => {
            this.validaceDataPatternElement(element);
        });
    }

    validaceDataPatternElement(element) {
        console.log('Spouštím validaci data-pattern pro element: ' + element.name);
        let chyba = $(element).attr('title') || 'Neplatný formát.';
        let dataPattern = $(element).data('pattern');
        let pattern = null;

        if (element.value.trim() === '') {
            return; // přeskočíme prázdné hodnoty
        }

        // Validace e-mailu
        if (dataPattern === 'email') {
            pattern = "^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\\.[a-zA-Z]{2,}$";

        // Validace opakovaného hesla
        } else if (dataPattern === 'password2') {
            if (!this.validaceShodnychHesel()) {
                console.error(element.name, chyba);
                $(element).addClass('is-invalid');
            }else{
                $(element).addClass('is-valid');
            }

        // Validace délky (např. data-pattern="8")
        } else if (!isNaN(dataPattern)) {
            if (!this.validaceMinLength(element.value.length, parseInt(dataPattern))) {
                console.error(element.name, chyba);
                $(element).addClass('is-invalid');
            }else{
                $(element).addClass('is-valid');
            }

        } else {
            pattern = dataPattern; // Použiji hodnotu z atributu jako regex pattern
        }

        if (pattern) {
            console.log('Používám vzor: ' + pattern + ' pro validaci: ' + element.name);
            const value = $(element).val();
            if (!this.validaceRegExp(value, pattern)) {
                console.error(element.name, chyba);
                $(element).addClass('is-invalid');
            }else{
                $(element).addClass('is-valid');
            }
        }
    }

    validaceRegExp(value, pattern) {
        try {
            const regex = new RegExp(pattern);
            return regex.test(value);
        } catch (e) {
            console.error('Chybný regulární výraz:', pattern);
            return false;
        }
    }

    validaceShodnychHesel() {
        console.log('Validuji shodnost hesel');
        const password = $('#password').val();
        const password2 = $('#password2').val();
        return (password === password2);
    }
}
