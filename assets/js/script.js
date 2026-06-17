document.addEventListener("DOMContentLoaded", () => {
    const deleteForms = document.querySelectorAll('.form-delete');
    deleteForms.forEach(form => {
        form.addEventListener('submit', function(e) {
            if(!confirm("Are you sure you want to delete this item?")) {
                e.preventDefault();
            }
        });
    });
});
