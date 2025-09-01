document.addEventListener('DOMContentLoaded', function () {
  const quantityInput = document.getElementById('editQuantity');
  const unitPriceInput = document.getElementById('editPrice');
  const totalAmountInput = document.getElementById('totalAmount');
  const modal = document.getElementById('editTransactionModal');

  function updateTotal() {
    const quantity = parseFloat(quantityInput.value) || 0;
    const unitPrice = parseFloat(unitPriceInput.value) || 0;
    const total = quantity * unitPrice;
    totalAmountInput.value = total.toFixed(2);
  }

  // Ensure the elements exist before attaching events
  if (quantityInput && unitPriceInput && totalAmountInput) {
    quantityInput.addEventListener('input', updateTotal);
    unitPriceInput.addEventListener('input', updateTotal);
  }

  // When the modal is shown, recalculate in case values are pre-filled
  if (modal) {
    modal.addEventListener('shown.bs.modal', function () {
      updateTotal();
    });
  }
});
