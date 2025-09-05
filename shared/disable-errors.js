// Quick fix to disable error notifications
// Add this script before other scripts if error popups are causing issues

// Disable error notifications temporarily
window.addEventListener('DOMContentLoaded', () => {
    // Override the error handler to be silent
    if (window.ErrorHandler) {
        window.ErrorHandler.showNotification = function() {
            // Do nothing - silent mode
        };
        window.ErrorHandler.showUserFriendlyError = function() {
            // Do nothing - silent mode
        };
    }
    
    // Override Utils notification if it exists
    if (window.Utils && window.Utils.showNotification) {
        window.Utils.showNotification = function() {
            // Do nothing - silent mode
        };
    }
    
    console.log('Error notifications disabled');
});
