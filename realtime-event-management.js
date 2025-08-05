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
        
        // Create SignalR connection using modern SignalR client
        signalRConnection = new signalR.HubConnectionBuilder()
            .withUrl("/eventHub")
            .withAutomaticReconnect([0, 2000, 10000, 30000]) // Retry delays
            .build();
        
        // Setup SignalR event handlers
        setupSignalREventHandlers();
        
        // Start the connection
        signalRConnection.start()
            .then(function () {
                console.log('SignalR connected successfully');
                isConnected = true;
                reconnectAttempts = 0;
                
                // Show connection status
                showConnectionStatus('Connected', 'success');
                
                // Join the event updates group
                signalRConnection.invoke("joinEventGroup", 0);
                
                // Initial data load
                updateViewBasedOnEventId();
            })
            .catch(function (error) {
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
        signalRConnection.onreconnecting(function (error) {
            console.log('SignalR reconnecting...', error);
            isConnected = false;
            showConnectionStatus('Reconnecting...', 'warning');
        });
        
        signalRConnection.onreconnected(function (connectionId) {
            console.log('SignalR reconnected with connection ID:', connectionId);
            isConnected = true;
            reconnectAttempts = 0;
            showConnectionStatus('Reconnected', 'success');
            
            // Rejoin groups and refresh data
            signalRConnection.invoke("joinEventGroup", 0);
            updateViewBasedOnEventId();
        });
        
        signalRConnection.onclose(function (error) {
            console.log('SignalR disconnected:', error);
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
    signalRConnection.on("eventSelected", function (data) {
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
    });
    
    // Handle event clearing notifications
    signalRConnection.on("eventCleared", function (data) {
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
    });
    
    // Handle operational hours update notifications
    signalRConnection.on("operationalHoursUpdated", function (data) {
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
    });
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
    var statusElement = document.getElementById('connection-status');
    var statusIndicator = document.getElementById('status-indicator');
    var statusText = document.getElementById('status-text');
    
    if (!statusElement || !statusIndicator || !statusText) {
        console.warn('Connection status elements not found');
        return;
    }
    
    // Set status text
    statusText.textContent = message;
    
    // Set indicator color based on type
    statusIndicator.className = 'w-3 h-3 rounded-full mr-2';
    switch (type) {
        case 'success':
            statusIndicator.classList.add('bg-green-500');
            break;
        case 'error':
            statusIndicator.classList.add('bg-red-500');
            break;
        case 'warning':
            statusIndicator.classList.add('bg-yellow-500');
            break;
        default:
            statusIndicator.classList.add('bg-blue-500');
    }
    
    // Show the status
    statusElement.classList.remove('hidden');
    
    // Auto hide after 5 seconds for success/warning
    if (type !== 'error') {
        setTimeout(function() {
            statusElement.classList.add('hidden');
        }, 5000);
    }
}

// Function to show real-time notification
function showRealTimeNotification(message, type) {
    // Create notification element
    var notification = document.createElement('div');
    notification.className = 'fixed top-20 right-4 z-40 bg-white rounded-lg shadow-lg p-3 border-l-4 max-w-sm';
    
    // Set border color based on type
    switch (type) {
        case 'success':
            notification.classList.add('border-green-500');
            break;
        case 'error':
            notification.classList.add('border-red-500');
            break;
        case 'warning':
            notification.classList.add('border-yellow-500');
            break;
        default:
            notification.classList.add('border-blue-500');
    }
    
    // Set content
    notification.innerHTML = `
        <div class="flex items-center">
            <div class="flex-shrink-0">
                <svg class="h-5 w-5 text-gray-400" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                </svg>
            </div>
            <div class="ml-3">
                <p class="text-sm font-medium text-gray-900">
                    <strong>Real-time:</strong> ${escapeHtml(message)}
                </p>
            </div>
            <div class="ml-auto pl-3">
                <button onclick="this.parentElement.parentElement.parentElement.remove()" class="text-gray-400 hover:text-gray-600">
                    <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                    </svg>
                </button>
            </div>
        </div>
    `;
    
    // Add to body
    document.body.appendChild(notification);
    
    // Auto remove after 3 seconds
    setTimeout(function() {
        if (notification.parentElement) {
            notification.remove();
        }
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
                // Server not available - show fallback content
                showFallbackContent(eventId);
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
    var defaultContent = `
        <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-gray-400" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-gray-800">
                        No Event Selected
                    </h3>
                    <div class="mt-2 text-sm text-gray-600">
                        <p>Please select an event to view operational hours and real-time updates.</p>
                    </div>
                </div>
            </div>
        </div>
    `;
    updateOperationalHoursContainer(defaultContent);
}

// Function to show loading state
function showLoadingState() {
    var container = document.getElementById('ophours-container');
    if (container) {
        container.innerHTML = `
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="animate-spin h-5 w-5 text-blue-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-blue-800">
                            Loading Event Data
                        </h3>
                        <div class="mt-2 text-sm text-blue-700">
                            <p>Fetching operational hours and real-time updates...</p>
                        </div>
                    </div>
                </div>
            </div>
        `;
    }
}

// Function to hide loading state
function hideLoadingState() {
    console.log('Loading completed');
}

// Function to show error state
function showErrorState(message) {
    var errorContent = '<p class="text-red-600"><svg class="inline w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path></svg>' + escapeHtml(message) + '</p>';
    updateOperationalHoursContainer(errorContent);
}

// Function to show fallback content when server is not available
function showFallbackContent(eventId) {
    var fallbackContent = `
        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-yellow-800">
                        Server Unavailable
                    </h3>
                    <div class="mt-2 text-sm text-yellow-700">
                        <p>Event ID: <strong>${escapeHtml(eventId)}</strong></p>
                        <p>Real-time updates are currently unavailable. The system will retry automatically when the server is back online.</p>
                    </div>
                </div>
            </div>
        </div>
    `;
    updateOperationalHoursContainer(fallbackContent);
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