// Wait for the DOM to be fully loaded
document.addEventListener('DOMContentLoaded', function() {
    if ( window.history.replaceState ) {
        window.history.replaceState( null, null, window.location.href );
    }
});
