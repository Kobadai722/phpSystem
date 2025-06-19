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

    const deleteButtons = document.querySelectorAll('.delete-employee-btn');
    const deleteConfirmModal = document.getElementById('deleteConfirmModal');

    if (deleteButtons.length > 0 && deleteConfirmModal) {
        const modalEmployeeNameSpan = deleteConfirmModal.querySelector('#modalEmployeeName');
        const modalEmployeeIdInput = deleteConfirmModal.querySelector('#modalEmployeeId');

        deleteButtons.forEach(button => {
            button.addEventListener('click', function() {
                const employeeId = this.dataset.employeeId;
                const employeeName = this.dataset.employeeName;

                if (modalEmployeeNameSpan) {
                    modalEmployeeNameSpan.textContent = employeeName;
                }
                if (modalEmployeeIdInput) {
                    modalEmployeeIdInput.value = employeeId;
                }
                // モーダルの表示はボタンの data-bs-toggle 属性によって自動的に行われます
            });
        });
    }
});