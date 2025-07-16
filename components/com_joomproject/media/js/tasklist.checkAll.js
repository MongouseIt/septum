function toggleTaskListCheckboxes(tasklistId) {
    var container = document.getElementById(tasklistId);
    if (!container) return;

    var checkAllBox = document.querySelector('[data-tasklist="' + tasklistId + '"]');
    var isChecked = checkAllBox ? checkAllBox.checked : false;

    // Find all checkboxes in this task list
    var checkboxes = container.querySelectorAll('input[type="checkbox"][name="cid[]"]');

    // Set all checkboxes to match the "check all" state
    checkboxes.forEach(function(checkbox) {
        checkbox.checked = isChecked;

        // Try to use Joomla's isChecked safely
        try {
            if (typeof Joomla !== 'undefined' && Joomla.isChecked) {
                // Make sure boxchecked exists first
                if (document.getElementById('boxchecked')) {
                    Joomla.isChecked(isChecked, checkbox.id.replace('cb', ''));
                }
            }
        } catch (e) {
            console.log('Could not call Joomla.isChecked, updating boxchecked manually');
        }
    });

    // Update boxchecked value manually to ensure it works
    var totalChecked = document.querySelectorAll('#adminForm input[name="cid[]"]:checked').length;
    if (document.getElementById('boxchecked')) {
        document.getElementById('boxchecked').value = totalChecked;
    }

    // Toggle toolbar buttons
    toggleToolbarButtons(totalChecked);
}

function toggleToolbarButtons(checkedCount) {
    // Use the specific button IDs from your HTML
    var buttons = document.querySelectorAll('#toolbar-batch, #btn-bulk');

    if (checkedCount > 0) {
        buttons.forEach(function(button) {
            button.classList.remove('disabled');
            button.removeAttribute('disabled');
        });
    } else {
        buttons.forEach(function(button) {
            button.classList.add('disabled');
            button.setAttribute('disabled', 'disabled');
        });
    }

    // Also try to call JPlist.toggleBulkButton if it exists
    try {
        if (typeof JPlist !== 'undefined' && JPlist.toggleBulkButton) {
            JPlist.toggleBulkButton();
        }
    } catch (e) {
        console.log('Could not call JPlist.toggleBulkButton');
    }
}

// Listen for checkbox changes to update toolbar state
document.addEventListener('change', function(e) {
    if (e.target.type === 'checkbox' && (e.target.name === 'cid[]' || e.target.classList.contains('tasklist-checkall'))) {
        var totalChecked = document.querySelectorAll('#adminForm input[name="cid[]"]:checked').length;

        if (document.getElementById('boxchecked')) {
            document.getElementById('boxchecked').value = totalChecked;
        }

        toggleToolbarButtons(totalChecked);
    }
});

// Initialize toolbar buttons state on page load
document.addEventListener('DOMContentLoaded', function() {
    // Wait a small amount of time to ensure all Joomla scripts have loaded
    setTimeout(function() {
        var totalChecked = document.querySelectorAll('#adminForm input[name="cid[]"]:checked').length;

        if (document.getElementById('boxchecked')) {
            document.getElementById('boxchecked').value = totalChecked;
        }

        toggleToolbarButtons(totalChecked);
    }, 100);
});