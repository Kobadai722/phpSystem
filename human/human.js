function clearInputField(spanElement){
    const targetInput = spanElement.previousElementSibling;
    if (targetInput && (targetInput.tagName === 'INPUT' || targetInput.tagName === 'TEXTAREA')) {
        targetInput.value = "";
        spanElement.style.display = 'none';
    }
}

document.addEventListener('DOMContentLoaded', function() {
    const inputsToWatch = [
        document.getElementById('name_keyword'),
        document.getElementById('id_keyword')
    ];
    inputsToWatch.forEach(inputElement => {
        if (inputElement) {
            const clearButton = inputElement.nextElementSibling;
            if (clearButton && clearButton.tagName === 'SPAN') {
                const updateVisibility = () => {
                    clearButton.style.display = inputElement.value.length > 0 ? '' : 'none';
                };
                updateVisibility();
                inputElement.addEventListener('input', updateVisibility);
            }
        }
    });
});