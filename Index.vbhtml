@ModelType IEnumerable(Of POPB_event)

@Code
    ViewData("Title") = "Event Management"
End Code

@section Scripts
    <!-- SignalR Scripts -->
    <script src="~/Scripts/jquery.signalR-2.4.3.min.js"></script>
    <script src="~/signalr/hubs"></script>
    <script src="~/Scripts/event-realtime.js"></script>
End Section

<div class="container-fluid">
    <!-- Notifications Container -->
    <div id="notifications-container"></div>

    <div class="row">
        <div class="col-md-8">
            <h2>Event Management</h2>
            
            <!-- Event List -->
            <div class="card">
                <div class="card-header">
                    <h5>Available Events</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Event Time</th>
                                    <th>Event Held</th>
                                    <th>Created On</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @For Each item In Model
                                    @<tr>
                                        <td>@item.id</td>
                                        <td>@item.event_time</td>
                                        <td>@item.event_held</td>
                                        <td>@item.created_on.ToString("dd/MM/yyyy HH:mm")</td>
                                        <td>
                                            <button class="btn btn-primary btn-sm" onclick="selectEventRealTime(@item.id)">
                                                Select Event
                                            </button>
                                            @Html.ActionLink("Details", "Details", New With {.id = item.id}, New With {.class = "btn btn-info btn-sm"})
                                            @Html.ActionLink("Edit", "Edit", New With {.id = item.id}, New With {.class = "btn btn-warning btn-sm"})
                                            @Html.ActionLink("Delete", "Delete", New With {.id = item.id}, New With {.class = "btn btn-danger btn-sm"})
                                        </td>
                                    </tr>
                                Next
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <!-- Selected Event Display -->
            <div class="card">
                <div class="card-header">
                    <h5>Selected Event</h5>
                </div>
                <div class="card-body">
                    <div id="selected-event-container">
                        @If ViewData("selectedEvent") IsNot Nothing Then
                            Dim selectedEvent = DirectCast(ViewData("selectedEvent"), POPB_event)
                            @<div class="alert alert-success">
                                <h6>Currently Selected Event</h6>
                                <p><strong>Time:</strong> @selectedEvent.event_time</p>
                                <p><strong>Day:</strong> @selectedEvent.event_held</p>
                            </div>
                        Else
                            @<div class="alert alert-info">
                                <p>No event selected. Please select an event from the list.</p>
                            </div>
                        End If
                    </div>
                    
                    <button class="btn btn-secondary btn-sm" onclick="clearSelectedEvent()">
                        Clear Selection
                    </button>
                </div>
            </div>

            <!-- Operational Hours Display -->
            <div class="card mt-3">
                <div class="card-header">
                    <h5>Operational Hours</h5>
                </div>
                <div class="card-body">
                    <div id="ophours-container">
                        @If ViewData("ophours") IsNot Nothing Then
                            Dim ophours = DirectCast(ViewData("ophours"), List(Of POPB_opday))
                            If ophours.Count > 0 Then
                                @<div class="alert alert-info">
                                    <h6>Operational Hours</h6>
                                    <ul class="list-unstyled">
                                        @For Each op In ophours
                                            @<li><strong>@op.value</strong></li>
                                        Next
                                    </ul>
                                </div>
                            Else
                                @<div class="alert alert-warning">
                                    <p>No operational hours available for the selected event.</p>
                                </div>
                            End If
                        Else
                            @<div class="alert alert-info">
                                <p>Select an event to view operational hours.</p>
                            </div>
                        End If
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Create New Event Button -->
    <div class="row mt-3">
        <div class="col-12">
            @Html.ActionLink("Create New Event", "Create", Nothing, New With {.class = "btn btn-success"})
        </div>
    </div>
</div>

<script>
    // Additional client-side functions
    $(document).ready(function() {
        // Auto-refresh operational hours every 30 seconds (fallback)
        setInterval(function() {
            if ($('#selected-event-container .alert-success').length > 0) {
                // If there's a selected event, refresh the data
                var eventId = getCurrentSelectedEventId();
                if (eventId) {
                    selectEventRealTime(eventId);
                }
            }
        }, 30000);
    });

    function getCurrentSelectedEventId() {
        // Extract event ID from the selected event display
        var selectedEventText = $('#selected-event-container').text();
        // This is a simple implementation - you might want to store the ID in a data attribute
        return null; // Implement based on your needs
    }
</script>