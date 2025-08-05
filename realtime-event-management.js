// Real-time Event Management with SignalR
// Global variables
var isUpdating = false;
var currentModal = null;
var signalRConnection = null;
var isConnected = false;
var reconnectAttempts = 0;
var maxReconnectAttempts = 5;
var reconnectInterval = 5000; // 5 seconds

// SignalR Connection Management
function initializeSignalR() {
    try {
        console.log('Initializing SignalR connection...');
        
        // Create SignalR connection
        signalRConnection = $.connection.eventHub;
        
        // Setup SignalR event handlers
        setupSignalREventHandlers();
        
        // Start the connection
        $.connection.hub.start()
            .done(function () {
                console.log('SignalR connected successfully');
                isConnected = true;
                reconnectAttempts = 0;
                
                // Show connection status
                showConnectionStatus('Connected', 'success');
                
                // Join the event updates group
                signalRConnection.server.joinEventGroup(0);
                
                // Initial data load
                updateViewBasedOnEventId();
            })
            .fail(function (error) {
                console.error('SignalR connection failed:', error);
                isConnected = false;
                showConnectionStatus('Connection failed', 'error');
                
                // Attempt to reconnect
                if (reconnectAttempts < maxReconnectAttempts) {
                    setTimeout(function() {
                        reconnectAttempts++;
                        console.log(`Reconnect attempt ${reconnectAttempts}/${maxReconnectAttempts}`);
                        initializeSignalR();
                    }, reconnectInterval);
                } else {
                    console.error('Max reconnection attempts reached');
                    showConnectionStatus('Connection lost', 'error');
                }
            });
            
        // Handle connection state changes
        $.connection.hub.connectionSlow(function () {
            console.log('SignalR connection is slow');
            showConnectionStatus('Connection slow', 'warning');
        });
        
        $.connection.hub.reconnecting(function () {
            console.log('SignalR reconnecting...');
            isConnected = false;
            showConnectionStatus('Reconnecting...', 'warning');
        });
        
        $.connection.hub.reconnected(function () {
            console.log('SignalR reconnected');
            isConnected = true;
            reconnectAttempts = 0;
            showConnectionStatus('Reconnected', 'success');
            
            // Rejoin groups and refresh data
            signalRConnection.server.joinEventGroup(0);
            updateViewBasedOnEventId();
        });
        
        $.connection.hub.disconnected(function () {
            console.log('SignalR disconnected');
            isConnected = false;
            showConnectionStatus('Disconnected', 'error');
            
            // Attempt to reconnect if not manually disconnected
            if (reconnectAttempts < maxReconnectAttempts) {
                setTimeout(function() {
                    reconnectAttempts++;
                    console.log(`Reconnect attempt ${reconnectAttempts}/${maxReconnectAttempts}`);
                    initializeSignalR();
                }, reconnectInterval);
            }
        });
        
    } catch (error) {
        console.error('Error initializing SignalR:', error);
        showConnectionStatus('SignalR initialization failed', 'error');
    }
}

// Setup SignalR event handlers
function setupSignalREventHandlers() {
    if (!signalRConnection) {
        console.error('SignalR connection not available');
        return;
    }
    
    // Handle event selection notifications
    signalRConnection.client.eventSelected = function (data) {
        console.log('SignalR: Event selected notification received:', data);
        
        try {
            // Update localStorage
            localStorage.setItem('SelectedEventId', data.eventId);
            
            // Update modal selection
            updateModalSelection(data.eventId);
            
            // Update view with new data
            updateOperationalHoursFromSignalR(data);
            
            // Show notification
            showRealTimeNotification('Event selected: ' + data.eventTime, 'success');
            
        } catch (error) {
            console.error('Error handling eventSelected notification:', error);
        }
    };
    
    // Handle event clearing notifications
    signalRConnection.client.eventCleared = function (data) {
        console.log('SignalR: Event cleared notification received:', data);
        
        try {
            // Clear localStorage
            localStorage.removeItem('SelectedEventId');
            
            // Reset modal selection
            updateModalSelection('');
            
            // Reset operational hours
            resetOperationalHours();
            
            // Show notification
            showRealTimeNotification('Event selection cleared', 'info');
            
        } catch (error) {
            console.error('Error handling eventCleared notification:', error);
        }
    };
    
    // Handle operational hours update notifications
    signalRConnection.client.operationalHoursUpdated = function (data) {
        console.log('SignalR: Operational hours updated notification received:', data);
        
        try {
            // Update operational hours display
            if (data.operationalHours && data.operationalHours.length > 0) {
                var ophoursList = '<ul class="list-unstyled mb-0">';
                data.operationalHours.forEach(function (oph) {
                    if (oph.value) {
                        ophoursList += '<li>' + escapeHtml(oph.value) + '</li>';
                    }
                });
                ophoursList += '</ul>';
                
                updateOperationalHoursContainer(ophoursList);
                
                // Show notification
                showRealTimeNotification('Operational hours updated for ' + data.targetPage, 'success');
            } else {
                resetOperationalHours();
                showRealTimeNotification('No operational hours available', 'warning');
            }
            
        } catch (error) {
            console.error('Error handling operationalHoursUpdated notification:', error);
        }
    };
}

// Function to update operational hours from SignalR data
function updateOperationalHoursFromSignalR(data) {
    if (data.operationalHours && data.operationalHours.length > 0) {
        var ophoursList = '<ul class="list-unstyled mb-0">';
        data.operationalHours.forEach(function (oph) {
            if (oph.value) {
                ophoursList += '<li>' + escapeHtml(oph.value) + '</li>';
            }
        });
        ophoursList += '</ul>';
        
        updateOperationalHoursContainer(ophoursList);
    } else {
        resetOperationalHours();
    }
}

// Function to show connection status
function showConnectionStatus(message, type) {
    var statusClass = 'alert-info';
    switch (type) {
        case 'success':
            statusClass = 'alert-success';
            break;
        case 'error':
            statusClass = 'alert-danger';
            break;
        case 'warning':
            statusClass = 'alert-warning';
            break;
    }
    
    var statusHtml = '<div class="alert ' + statusClass + ' alert-dismissible fade show connection-status" role="alert">' +
        '<i class="fa fa-signal"></i> ' + escapeHtml(message) +
        '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>' +
        '</div>';
    
    // Remove existing status
    $('.connection-status').remove();
    
    // Add new status
    var container = $('.container').first() || $('body');
    container.prepend(statusHtml);
    
    // Auto remove after 5 seconds for success/warning
    if (type !== 'error') {
        setTimeout(function() {
            $('.connection-status').fadeOut();
        }, 5000);
    }
}

// Function to show real-time notification
function showRealTimeNotification(message, type) {
    var iconClass = 'fa-info-circle';
    var alertClass = 'alert-info';
    
    switch (type) {
        case 'success':
            iconClass = 'fa-check-circle';
            alertClass = 'alert-success';
            break;
        case 'error':
            iconClass = 'fa-exclamation-triangle';
            alertClass = 'alert-danger';
            break;
        case 'warning':
            iconClass = 'fa-exclamation-triangle';
            alertClass = 'alert-warning';
            break;
    }
    
    var notificationHtml = '<div class="alert ' + alertClass + ' alert-dismissible fade show realtime-notification" role="alert">' +
        '<i class="fa ' + iconClass + '"></i> <strong>Real-time:</strong> ' + escapeHtml(message) +
        '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>' +
        '</div>';
    
    // Add notification
    var container = $('.container').first() || $('body');
    container.prepend(notificationHtml);
    
    // Auto remove after 3 seconds
    setTimeout(function() {
        $('.realtime-notification').fadeOut(function() {
            $(this).remove();
        });
    }, 3000);
}

// Main function to update view based on selected event ID
function updateViewBasedOnEventId() {
    // Prevent multiple simultaneous calls
    if (isUpdating) {
        console.log('Update already in progress, skipping...');
        return;
    }

    var eventId = localStorage.getItem('SelectedEventId');
    console.log('EventId from storage:', eventId);

    if (!eventId || eventId === 'null' || eventId === 'undefined') {
        console.log('No valid event ID found in localStorage');
        resetOperationalHours();
        return;
    }

    isUpdating = true;
    showLoadingState();

    // Perform AJAX call to fetch event data from the server
    $.ajax({
        url: '/Event/SelectEventDetails',
        type: 'GET',
        data: { eventId: eventId },
        timeout: 10000, // 10 second timeout
        success: function (data) {
            console.log('Response data:', data);

            try {
                // Check if response is successful
                if (data && data.success) {
                    // Update the operational hours display
                    if (data.ophours && data.ophours.length > 0) {
                        var ophoursList = '<ul class="list-unstyled mb-0">';
                        data.ophours.forEach(function (oph) {
                            if (oph.value) {
                                ophoursList += '<li>' + escapeHtml(oph.value) + '</li>';
                            }
                        });
                        ophoursList += '</ul>';

                        // Update the DOM with operational hours
                        updateOperationalHoursContainer(ophoursList);
                        console.log('Operational hours updated successfully');

                        // Update modal selection if modal exists
                        updateModalSelection(eventId);
                    } else {
                        // No operational hours available
                        resetOperationalHours();
                        console.log('No operational hours found for event ID:', eventId);
                    }
                } else {
                    // Handle unsuccessful response
                    console.error('Unsuccessful response:', data.message || 'Unknown error');
                    showErrorState(data.message || 'Failed to load data');
                }
            } catch (error) {
                console.error('Error processing response data:', error);
                showErrorState('Error processing data');
            }
        },
        error: function (xhr, status, error) {
            console.error('AJAX Error - Status:', status);
            console.error('AJAX Error - Error:', error);
            console.error('AJAX Error - Response:', xhr.responseText);
            console.error('AJAX Error - Status Code:', xhr.status);

            // Handle different error types
            if (xhr.status === 404) {
                showErrorState('Event not found');
                // Clear invalid event from storage
                localStorage.removeItem('SelectedEventId');
            } else if (xhr.status === 500) {
                showErrorState('Server error occurred');
            } else if (status === 'timeout') {
                showErrorState('Request timeout');
            } else {
                showErrorState('Error loading data');
            }
        },
        complete: function () {
            isUpdating = false;
            hideLoadingState();
        }
    });
}

// Function to update operational hours container
function updateOperationalHoursContainer(content) {
    var container = document.getElementById('ophours-container');
    if (container) {
        container.innerHTML = content;
    } else {
        console.error('ophours-container element not found');
    }
}

// Function to reset operational hours to default state
function resetOperationalHours() {
    var defaultContent = '<p>No operational hours available for the selected event.</p>';
    updateOperationalHoursContainer(defaultContent);
}

// Function to show loading state
function showLoadingState() {
    var container = document.getElementById('ophours-container');
    if (container) {
        container.innerHTML = '<p><i class="fa fa-spinner fa-spin"></i> Loading operational hours...</p>';
    }
}

// Function to hide loading state
function hideLoadingState() {
    console.log('Loading completed');
}

// Function to show error state
function showErrorState(message) {
    var errorContent = '<p class="text-danger"><i class="fa fa-exclamation-triangle"></i> ' + escapeHtml(message) + '</p>';
    updateOperationalHoursContainer(errorContent);
}

// Function to escape HTML to prevent XSS
function escapeHtml(text) {
    var map = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    };
    return text.replace(/[&<>"']/g, function (m) { return map[m]; });
}

// Function to update modal selection based on current event
function updateModalSelection(eventId) {
    var eventSelect = document.getElementById('eventSelect');
    if (eventSelect && eventId) {
        eventSelect.value = eventId;
        console.log('Modal selection updated to event ID:', eventId);
    }
}

// Function to handle modal form submission
function handleModalFormSubmission() {
    var form = document.querySelector('#selectEventModal form');
    if (form) {
        form.addEventListener('submit', function (e) {
            e.preventDefault(); // Prevent default form submission

            var eventId = document.getElementById('eventSelect').value;

            if (!eventId) {
                alert('Please select an event');
                return;
            }

            console.log('Modal form submitted with event ID:', eventId);

            // Show loading in modal
            var submitBtn = form.querySelector('button[type="submit"]');
            var originalText = submitBtn.innerHTML;
            submitBtn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Loading...';
            submitBtn.disabled = true;

            // Store in localStorage
            localStorage.setItem('SelectedEventId', eventId);

            // Update view immediately
            updateViewBasedOnEventId();

            // Close modal after short delay
            setTimeout(function () {
                var modal = bootstrap.Modal.getInstance(document.getElementById('selectEventModal'));
                if (modal) {
                    modal.hide();
                }

                // Reset button
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;

                // Show success message
                showSuccessMessage('Event selected successfully');
            }, 1000);
        });
    }
}

// Function to show success message
function showSuccessMessage(message) {
    // Create and show a toast or alert
    var alertHtml = '<div class="alert alert-success alert-dismissible fade show" role="alert">' +
        '<i class="fa fa-check-circle"></i> ' + escapeHtml(message) +
        '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>' +
        '</div>';

    // Try to find a container for alerts
    var alertContainer = document.getElementById('alert-container') || document.querySelector('.container').firstElementChild;
    if (alertContainer) {
        var tempDiv = document.createElement('div');
        tempDiv.innerHTML = alertHtml;
        alertContainer.parentNode.insertBefore(tempDiv.firstChild, alertContainer);

        // Auto remove after 5 seconds
        setTimeout(function () {
            var alert = document.querySelector('.alert-success');
            if (alert) {
                alert.remove();
            }
        }, 5000);
    }
}

// Function to handle modal events
function setupModalEventListeners() {
    var modal = document.getElementById('selectEventModal');
    if (modal) {
        // When modal is shown, update the selection based on current event
        modal.addEventListener('shown.bs.modal', function () {
            console.log('Modal shown');
            currentModal = this;

            var currentEventId = localStorage.getItem('SelectedEventId');
            if (currentEventId) {
                updateModalSelection(currentEventId);
            }
        });

        // When modal is hidden, clear current modal reference
        modal.addEventListener('hidden.bs.modal', function () {
            console.log('Modal hidden');
            currentModal = null;
        });

        // Handle event selection change in modal
        var eventSelect = document.getElementById('eventSelect');
        if (eventSelect) {
            eventSelect.addEventListener('change', function () {
                var selectedEventId = this.value;
                console.log('Event selection changed in modal:', selectedEventId);

                // Enable/disable submit button based on selection
                var submitBtn = modal.querySelector('button[type="submit"]');
                if (submitBtn) {
                    submitBtn.disabled = !selectedEventId;
                }
            });
        }
    }
}

// Function to open event selection modal
function openEventSelectionModal() {
    var modal = document.getElementById('selectEventModal');
    if (modal) {
        var bsModal = new bootstrap.Modal(modal);
        bsModal.show();
    } else {
        console.error('Event selection modal not found');
    }
}

// Function to set event and close modal (for programmatic use)
function setSelectedEventAndCloseModal(eventId) {
    if (!eventId) {
        console.error('Invalid event ID provided');
        return;
    }

    console.log('Setting selected event ID programmatically:', eventId);

    // Store in localStorage
    localStorage.setItem('SelectedEventId', eventId);

    // Update modal selection
    updateModalSelection(eventId);

    // Update view
    updateViewBasedOnEventId();

    // Close modal if open
    if (currentModal) {
        var modal = bootstrap.Modal.getInstance(currentModal);
        if (modal) {
            modal.hide();
        }
    }

    // Dispatch custom event
    window.dispatchEvent(new CustomEvent('eventSelected', {
        detail: { eventId: eventId }
    }));
}

// Function to clear selected event
function clearSelectedEvent() {
    console.log('Clearing selected event');
    localStorage.removeItem('SelectedEventId');
    resetOperationalHours();

    // Reset modal selection
    var eventSelect = document.getElementById('eventSelect');
    if (eventSelect) {
        eventSelect.value = '';
    }

    // Dispatch custom event
    window.dispatchEvent(new CustomEvent('eventCleared'));
}

// Function to get current selected event ID
function getSelectedEventId() {
    return localStorage.getItem('SelectedEventId');
}

// Function to refresh operational hours data
function refreshOperationalHours() {
    console.log('Manually refreshing operational hours');
    updateViewBasedOnEventId();
}

// Function to check and update event (separated for multiple calls)
function checkAndUpdateEvent() {
    var existingEventId = localStorage.getItem('SelectedEventId');
    console.log('Checking for existing event ID:', existingEventId);

    if (existingEventId && existingEventId !== 'null' && existingEventId !== 'undefined') {
        console.log('Found existing event ID, updating view:', existingEventId);
        updateViewBasedOnEventId();
        updateModalSelection(existingEventId);
    } else {
        console.log('No existing event ID found, showing default state');
        resetOperationalHours();
    }
}

// Force refresh function that can be called manually
function forceRefreshEvent() {
    console.log('Force refreshing event data...');
    var eventId = localStorage.getItem('SelectedEventId');
    console.log('Current stored event ID:', eventId);

    if (eventId && eventId !== 'null' && eventId !== 'undefined') {
        // Force update regardless of isUpdating flag
        isUpdating = false;
        updateViewBasedOnEventId();
        return true;
    } else {
        resetOperationalHours();
        return false;
    }
}

// Debug function to log current state
function debugEventState() {
    console.log('=== Event Management Debug Info ===');
    console.log('Selected Event ID:', localStorage.getItem('SelectedEventId'));
    console.log('Is Updating:', isUpdating);
    console.log('Current Modal:', currentModal);
    console.log('Container Element:', document.getElementById('ophours-container'));
    console.log('Modal Element:', document.getElementById('selectEventModal'));
    console.log('Event Select Element:', document.getElementById('eventSelect'));
    console.log('SignalR Connected:', isConnected);
    console.log('SignalR Connection:', signalRConnection);
    console.log('====================================');
}

// Listen for sessionStorage changes across tabs/windows
window.addEventListener('storage', function (e) {
    console.log('Storage event detected:', e.key, e.newValue);

    if (e.key === 'SelectedEventId') {
        if (e.newValue && e.newValue !== 'null') {
            console.log('Event ID changed in another tab, updating view');
            updateViewBasedOnEventId();
            updateModalSelection(e.newValue);
        } else {
            console.log('Event ID cleared in another tab');
            resetOperationalHours();
            updateModalSelection('');
        }
    }
});

// Listen for custom events
window.addEventListener('eventSelected', function (e) {
    console.log('Custom eventSelected event received:', e.detail.eventId);
});

window.addEventListener('eventCleared', function (e) {
    console.log('Custom eventCleared event received');
});

// Document ready function
$(document).ready(function () {
    console.log('Document ready, initializing real-time event management');

    // Initialize SignalR connection
    initializeSignalR();

    // Setup modal event listeners
    setupModalEventListeners();

    // Setup form submission handler
    handleModalFormSubmission();

    // Force check multiple times to ensure it works in new windows
    setTimeout(function () {
        checkAndUpdateEvent();
    }, 100);

    setTimeout(function () {
        checkAndUpdateEvent();
    }, 500);

    setTimeout(function () {
        checkAndUpdateEvent();
    }, 1000);
});

// Window load event
window.addEventListener('load', function () {
    console.log('Window fully loaded, double-checking event');
    setTimeout(function () {
        checkAndUpdateEvent();
    }, 200);
});

// Visibility change event
document.addEventListener('visibilitychange', function () {
    if (!document.hidden) {
        // Page became visible, check if we need to refresh data
        console.log('Page became visible, checking for updates');
        var currentEventId = localStorage.getItem('SelectedEventId');
        if (currentEventId && currentEventId !== 'null') {
            // Small delay to ensure smooth transition
            setTimeout(function () {
                updateViewBasedOnEventId();
            }, 100);
        }
    }
});

// Utility function to check if localStorage is available
function isLocalStorageAvailable() {
    try {
        var test = '__localStorage_test__';
        localStorage.setItem(test, test);
        localStorage.removeItem(test);
        return true;
    } catch (e) {
        console.error('LocalStorage is not available:', e);
        return false;
    }
}

// Initialize localStorage check
if (!isLocalStorageAvailable()) {
    console.error('LocalStorage is not supported in this browser');
    showErrorState('Browser storage not supported');
}

// Expose functions globally for external use
window.EventManager = {
    openModal: openEventSelectionModal,
    setSelectedEvent: setSelectedEventAndCloseModal,
    clearSelectedEvent: clearSelectedEvent,
    getSelectedEventId: getSelectedEventId,
    refreshOperationalHours: refreshOperationalHours,
    updateViewBasedOnEventId: updateViewBasedOnEventId,
    forceRefresh: forceRefreshEvent,
    debugEventState: debugEventState,
    isConnected: function() { return isConnected; },
    reconnect: function() { 
        reconnectAttempts = 0;
        initializeSignalR();
    }
};

// For backward compatibility
window.updateViewBasedOnEventId = updateViewBasedOnEventId;
window.openEventSelectionModal = openEventSelectionModal;