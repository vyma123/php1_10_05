document.getElementById('price_from').addEventListener('input', function (e) {
    // Allow only numbers and decimal point
    this.value = this.value.replace(/[^0-9.]/g, '');
});

document.getElementById('price_to').addEventListener('input', function (e) {
    // Allow only numbers and decimal point
    this.value = this.value.replace(/[^0-9.]/g, '');
});