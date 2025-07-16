document.addEventListener('DOMContentLoaded', function() {

    var clearButton = document.getElementById('jpClearFilterButton');

    if (!clearButton) {
        console.error('Clear button not found');
        return;
    }

    clearButton.onclick = function() {
        var filterContainer = document.getElementById('filters');
        if (!filterContainer) {
            console.error('Filters container not found');
            return;
        }

        // Clear the search input if it exists in the container
        var searchInput = filterContainer.querySelector('#filter_search');
        if (searchInput) {
            searchInput.value = '';
        }

        // Reset all select elements within the container
        var selects = filterContainer.querySelectorAll('select');
        selects.forEach(function(select) {
            select.selectedIndex = 0;
        });

        // Uncheck all checkboxes within the container
        var checkboxes = filterContainer.querySelectorAll('input[type="checkbox"]');
        checkboxes.forEach(function(checkbox) {
            checkbox.checked = false;
        });

        // Submit the form if it exists
        if (this.form) {
            this.form.submit();
        } else {
            console.error('Filter Form not found');
        }
    };
});