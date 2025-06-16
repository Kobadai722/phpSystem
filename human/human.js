function clearInputField(spanElement){
    var targetInput = spanElement.previousElementSibling; // spanの直前にあるinput要素を取得
    if (targetInput && (targetInput.tagName === 'INPUT' || targetInput.tagName === 'TEXTAREA')) {
        targetInput.value = "";
    }
}