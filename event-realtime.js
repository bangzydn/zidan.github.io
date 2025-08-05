// SignalR connection for real-time event updates
$(document).ready(function () {
    // Initialize SignalR connection
    var eventHub = $.connection.eventHub;

    // Handle incoming event updates
    eventHub.client.eventSelected = function (data) {
        console.log('Real-time event update received:', data);
        
        if (data.success) {
            // Update the UI with new operational hours
            updateOperationalHoursDisplay(data);
            
            // Show success message
            showNotification('Event updated in real-time: ' + data.eventTime, 'success');
        } else {
            showNotification('Error updating event: ' + data.message, 'error');
        }
    };

    // Handle connection events
    $.connection.hub.start()
        .done(function () {
            console.log('SignalR connected successfully');
            showNotification('Real-time connection established', 'info');
        })
        .fail(function (error) {
            console.error('SignalR connection failed:', error);
            showNotification('Real-time connection failed', 'error');
        });

    // Function to update operational hours display
    function updateOperationalHoursDisplay(data) {
        var ophoursContainer = $('#ophours-container');
        
        if (ophoursContainer.length > 0) {
            // Clear existing content
            ophoursContainer.empty();
            
            // Add new operational hours
            if (data.ophours && data.ophours.length > 0) {
                var html = '<div class="alert alert-info">';
                html += '<h5>Operational Hours for ' + data.eventHeld + '</h5>';
                html += '<ul class="list-unstyled">';
                
                data.ophours.forEach(function(op) {
                    html += '<li><strong>' + op.value + '</strong></li>';
                });
                
                html += '</ul>';
                html += '</div>';
                
                ophoursContainer.html(html);
            } else {
                ophoursContainer.html('<div class="alert alert-warning">No operational hours available for this event.</div>');
            }
        }
        
        // Update selected event display
        updateSelectedEventDisplay(data);
    }

    // Function to update selected event display
    function updateSelectedEventDisplay(data) {
        var selectedEventContainer = $('#selected-event-container');
        
        if (selectedEventContainer.length > 0) {
            var html = '<div class="alert alert-success">';
            html += '<h5>Currently Selected Event</h5>';
            html += '<p><strong>Time:</strong> ' + data.eventTime + '</p>';
            html += '<p><strong>Day:</strong> ' + data.eventHeld + '</p>';
            html += '</div>';
            
            selectedEventContainer.html(html);
        }
    }

    // Function to show notifications
    function showNotification(message, type) {
        var alertClass = 'alert-info';
        
        switch (type) {
            case 'success':
                alertClass = 'alert-success';
                break;
            case 'error':
                alertClass = 'alert-danger';
                break;
            case 'warning':
                alertClass = 'alert-warning';
                break;
        }
        
        var notification = '<div class="alert ' + alertClass + ' alert-dismissible fade show" role="alert">';
        notification += message;
        notification += '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>';
        notification += '</div>';
        
        // Add notification to page
        $('#notifications-container').prepend(notification);
        
        // Auto-remove after 5 seconds
        setTimeout(function() {
            $('.alert').first().fadeOut();
        }, 5000);
    }

    // Enhanced event selection function with real-time broadcast
    window.selectEventRealTime = function(eventId) {
        $.ajax({
            url: '/Event/SelectEventDetails',
            type: 'POST',
            data: { eventId: eventId },
            success: function(response) {
                if (response.success) {
                    console.log('Event selected successfully:', response);
                    updateOperationalHoursDisplay(response);
                    showNotification('Event selected: ' + response.eventTime, 'success');
                } else {
                    showNotification('Error selecting event: ' + response.message, 'error');
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX error:', error);
                showNotification('Error selecting event: ' + error, 'error');
            }
        });
    };

    // Function to clear selected event
    window.clearSelectedEvent = function() {
        $.ajax({
            url: '/Event/ClearSelectedEvent',
            type: 'POST',
            success: function(response) {
                if (response.success) {
                    $('#ophours-container').empty();
                    $('#selected-event-container').empty();
                    showNotification('Event selection cleared', 'success');
                } else {
                    showNotification('Error clearing event: ' + response.message, 'error');
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX error:', error);
                showNotification('Error clearing event: ' + error, 'error');
            }
        });
    };
});