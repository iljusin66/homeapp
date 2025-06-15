let cnfd_formSubmitted = false;
document.addEventListener("DOMContentLoaded", function () {
    confirmNeulozenaFormData();
});

// Funkce pro uložení stavu formuláře, ale jen pro prvky s atributem data-kontrolaZmeny
const cnfd_getFormData = function(form) {
    const formData = new FormData();
    const elements = form.querySelectorAll('[data-kontrolaZmeny]');

    elements.forEach(element => {
        if (element.name) {
            formData.append(element.name, element.value);
        }
    });

    return formData;
}

// Funkce pro porovnání dvou FormData objektů
const cnfd_hasFormChanged = function(originalData, currentData) {
    for (let key of originalData.keys()) {
        if (originalData.get(key) !== currentData.get(key)) {
            return true;
        }
    }
    return false;
}

const confirmNeulozenaFormData = function() {
    // Inicializace
    const form = document.querySelector('form');
    const originalFormData = cnfd_getFormData(form);
    
    form.addEventListener('submit', () => {
        cnfd_formSubmitted = true;
    });
    
    // Přidání posluchače k formulářovým prvkům
    form.addEventListener('input', () => {
        const currentFormData = cnfd_getFormData(form);
        window.formChanged = cnfd_hasFormChanged(originalFormData, currentFormData);
    });

    // Přidání posluchače události beforeunload
    window.addEventListener('beforeunload', (event) => {
        if (!cnfd_formSubmitted && window.formChanged) {
            event.preventDefault();
            event.returnValue = 'Máte neuložené změny. Opravdu chcete opustit stránku?';
        }
    });

}





