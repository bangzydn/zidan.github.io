Imports Microsoft.AspNet.SignalR
Imports Newtonsoft.Json

Public Class EventController
    Inherits Controller

    Private db As New YourDbContext() ' Replace with your actual DbContext
    Private hubContext As IHubContext = GlobalHost.ConnectionManager.GetHubContext(Of EventHub)()

    ' POST: EventController/SelectEvent
    <HttpPost>
    Function SelectEvent(eventId As Integer) As ActionResult
        Try
            Debug.WriteLine("SelectEvent POST - Received eventId: " & eventId.ToString())

            ' Validate eventId
            If eventId <= 0 Then
                Debug.WriteLine("Invalid eventId received: " & eventId)
                TempData("ErrorMessage") = "Invalid event ID"
                Return RedirectToAction("Index")
            End If

            ' Find the selected event by eventId
            Dim selectedEvent = db.POPB_event.Find(eventId)

            ' If the event is not found, return error
            If selectedEvent Is Nothing Then
                Debug.WriteLine("Event not found with ID: " & eventId)
                TempData("ErrorMessage") = "Event not found"
                Return RedirectToAction("Index")
            End If

            ' Clear existing session data to prevent conflicts
            Session("SelectedEventId") = Nothing

            ' Store the selected event ID in the session
            Session("SelectedEventId") = selectedEvent.id

            ' Debugging output to check the values
            Debug.WriteLine("Event Selected Successfully: ID=" & selectedEvent.id & ", Time=" & selectedEvent.event_time & ", Held=" & selectedEvent.event_held)

            ' Determine target page based on event_held value
            Dim targetPage As String = "jumat" ' Default
            If Not String.IsNullOrEmpty(selectedEvent.event_held) Then
                If selectedEvent.event_held.ToLower().Contains("thursday") OrElse selectedEvent.event_held.ToLower().Contains("kamis") Then
                    targetPage = "kamis"
                End If
            End If

            ' Notify all connected clients about the event selection via SignalR
            hubContext.Clients.All.eventSelected(New With {
                .eventId = selectedEvent.id,
                .eventTime = selectedEvent.event_time,
                .eventHeld = selectedEvent.event_held,
                .targetPage = targetPage,
                .timestamp = DateTime.Now
            })

            ' Set success message
            TempData("SuccessMessage") = "Event with time '" & selectedEvent.event_time & "' selected successfully"

            ' Redirect to the Index action to show updated view
            Return RedirectToAction("Index")

        Catch ex As Exception
            Debug.WriteLine("Error in SelectEvent: " & ex.Message)
            Debug.WriteLine("Stack trace: " & ex.StackTrace)
            TempData("ErrorMessage") = "An error occurred while selecting the event"
            Return RedirectToAction("Index")
        End Try
    End Function

    ' GET: EventController/SelectEventDetails - AJAX endpoint
    <HttpGet>
    Function SelectEventDetails(eventId As Integer) As ActionResult
        Try
            Debug.WriteLine("SelectEventDetails AJAX - Received eventId: " & eventId.ToString())

            ' Validate eventId
            If eventId <= 0 Then
                Debug.WriteLine("Invalid eventId received: " & eventId)
                Return Json(New With {
                .success = False,
                .message = "Invalid event ID",
                .ophours = New List(Of Object)()
            }, JsonRequestBehavior.AllowGet)
            End If

            ' Find the selected event by eventId
            Dim selectedEvent = db.POPB_event.Find(eventId)

            ' If the event is not found, return error response
            If selectedEvent Is Nothing Then
                Debug.WriteLine("Event not found with ID: " & eventId)
                Return Json(New With {
                .success = False,
                .message = "Event not found",
                .ophours = New List(Of Object)()
            }, JsonRequestBehavior.AllowGet)
            End If

            ' Store the selected event ID in the session
            Session("SelectedEventId") = selectedEvent.id

            ' Debugging output to check the values
            Debug.WriteLine("AJAX Event Selected: ID=" & selectedEvent.id & ", Time=" & selectedEvent.event_time & ", Held=" & selectedEvent.event_held)

            ' Determine target page based on event_held value
            Dim targetPage As String = "jumat" ' Default
            If Not String.IsNullOrEmpty(selectedEvent.event_held) Then
                If selectedEvent.event_held.ToLower().Contains("thursday") OrElse selectedEvent.event_held.ToLower().Contains("kamis") Then
                    targetPage = "kamis"
                End If
            End If

            Debug.WriteLine("Target page determined: " & targetPage)

            ' Get operational hours based on the selected targetPage
            Dim operationalHours = db.POPB_opday.Where(Function(op) op.page.ToLower() = targetPage).ToList()

            ' Debugging: Log operational hours details
            Debug.WriteLine("Operational hours count for page '" & targetPage & "': " & operationalHours.Count)
            For Each op In operationalHours
                Debug.WriteLine("Op hour ID: " & op.id & ", Value: " & op.value & ", Page: " & op.page)
            Next

            ' Create response object with operational hours
            Dim response = New With {
            .success = True,
            .message = "Data retrieved successfully",
            .eventId = selectedEvent.id,
            .eventTime = selectedEvent.event_time,
            .eventHeld = selectedEvent.event_held,
            .targetPage = targetPage,
            .ophours = operationalHours.Select(Function(op) New With {
                .id = op.id,
                .value = If(String.IsNullOrEmpty(op.value), "No time specified", op.value),
                .page = op.page
            }).ToList()
        }

            Debug.WriteLine("Response JSON: " & JsonConvert.SerializeObject(response))

            ' Notify all connected clients about the operational hours update via SignalR
            Dim operationalHoursForSignalR = operationalHours.Select(Function(op) New With {
                .id = op.id,
                .value = If(String.IsNullOrEmpty(op.value), "No time specified", op.value),
                .page = op.page
            }).ToList()

            hubContext.Clients.All.operationalHoursUpdated(New With {
                .targetPage = targetPage,
                .operationalHours = operationalHoursForSignalR,
                .eventId = selectedEvent.id,
                .timestamp = DateTime.Now
            })

            ' Return JSON data for AJAX call
            Return Json(response, JsonRequestBehavior.AllowGet)

        Catch ex As Exception
            Debug.WriteLine("Error in SelectEventDetails: " & ex.Message)
            Debug.WriteLine("Stack trace: " & ex.StackTrace)

            ' Return error response
            Return Json(New With {
            .success = False,
            .message = "An error occurred while retrieving event details: " & ex.Message,
            .ophours = New List(Of Object)()
        }, JsonRequestBehavior.AllowGet)
        End Try
    End Function

    ' GET: EventController/GetAllEvents - AJAX endpoint to get all events
    <HttpGet>
    Function GetAllEvents() As ActionResult
        Try
            Dim events = db.POPB_event.Select(Function(e) New With {
            .id = e.id,
            .event_held = e.event_held
        }).OrderBy(Function(e) e.id).ToList()

            Return Json(New With {
            .success = True,
            .events = events
        }, JsonRequestBehavior.AllowGet)

        Catch ex As Exception
            Debug.WriteLine("Error in GetAllEvents: " & ex.Message)
            Return Json(New With {
            .success = False,
            .message = ex.Message,
            .events = New List(Of Object)()
        }, JsonRequestBehavior.AllowGet)
        End Try
    End Function

    ' GET: EventController/GetOperationalHours - Get operational hours for specific page
    <HttpGet>
    Function GetOperationalHours(page As String) As ActionResult
        Try
            If String.IsNullOrEmpty(page) Then
                Return Json(New With {
                .success = False,
                .message = "Page parameter is required",
                .ophours = New List(Of Object)()
            }, JsonRequestBehavior.AllowGet)
            End If

            Dim operationalHours = db.POPB_opday.Where(Function(op) op.page.ToLower() = page.ToLower()).ToList()

            Return Json(New With {
            .success = True,
            .page = page,
            .ophours = operationalHours.Select(Function(op) New With {
                .id = op.id,
                .value = op.value,
                .page = op.page
            }).ToList()
        }, JsonRequestBehavior.AllowGet)

        Catch ex As Exception
            Debug.WriteLine("Error in GetOperationalHours: " & ex.Message)
            Return Json(New With {
            .success = False,
            .message = ex.Message,
            .ophours = New List(Of Object)()
        }, JsonRequestBehavior.AllowGet)
        End Try
    End Function

    ' POST: EventController/ClearSelectedEvent - Clear session
    <HttpPost>
    Function ClearSelectedEvent() As ActionResult
        Try
            Session("SelectedEventId") = Nothing
            Debug.WriteLine("Selected event cleared from session")

            ' Notify all connected clients about the event clearing via SignalR
            hubContext.Clients.All.eventCleared(New With {
                .timestamp = DateTime.Now
            })

            If Request.IsAjaxRequest() Then
                Return Json(New With {
                .success = True,
                .message = "Event selection cleared"
            })
            Else
                TempData("SuccessMessage") = "Event selection cleared"
                Return RedirectToAction("Index")
            End If

        Catch ex As Exception
            Debug.WriteLine("Error in ClearSelectedEvent: " & ex.Message)

            If Request.IsAjaxRequest() Then
                Return Json(New With {
                .success = False,
                .message = ex.Message
            })
            Else
                TempData("ErrorMessage") = "Error clearing event selection"
                Return RedirectToAction("Index")
            End If
        End Try
    End Function

    ' GET: EventController/GetCurrentSelectedEvent - Get current selected event from session
    <HttpGet>
    Function GetCurrentSelectedEvent() As ActionResult
        Try
            Dim selectedEventId As Integer = If(Session("SelectedEventId"), 0)

            If selectedEventId = 0 Then
                Return Json(New With {
                .success = True,
                .selectedEvent = Nothing,
                .message = "No event selected"
            }, JsonRequestBehavior.AllowGet)
            End If

            Dim selectedEvent = db.POPB_event.Find(selectedEventId)

            If selectedEvent Is Nothing Then
                ' Clear invalid session data
                Session("SelectedEventId") = Nothing
                Return Json(New With {
                .success = False,
                .selectedEvent = Nothing,
                .message = "Selected event no longer exists"
            }, JsonRequestBehavior.AllowGet)
            End If

            Return Json(New With {
            .success = True,
            .selectedEvent = New With {
                .id = selectedEvent.id,
                .event_time = selectedEvent.event_time,
                .event_held = selectedEvent.event_held
            },
            .message = "Current selected event retrieved"
        }, JsonRequestBehavior.AllowGet)

        Catch ex As Exception
            Debug.WriteLine("Error in GetCurrentSelectedEvent: " & ex.Message)
            Return Json(New With {
            .success = False,
            .selectedEvent = Nothing,
            .message = ex.Message
        }, JsonRequestBehavior.AllowGet)
        End Try
    End Function

    ' POST: EventController/UpdateOperationalHours - Update operational hours and notify all clients
    <HttpPost>
    Function UpdateOperationalHours(page As String) As ActionResult
        Try
            If String.IsNullOrEmpty(page) Then
                Return Json(New With {
                .success = False,
                .message = "Page parameter is required"
            })
            End If

            ' Get updated operational hours
            Dim operationalHours = db.POPB_opday.Where(Function(op) op.page.ToLower() = page.ToLower()).ToList()

            ' Create operational hours object for SignalR
            Dim operationalHoursForSignalR = operationalHours.Select(Function(op) New With {
                .id = op.id,
                .value = If(String.IsNullOrEmpty(op.value), "No time specified", op.value),
                .page = op.page
            }).ToList()

            ' Notify all connected clients about the operational hours update via SignalR
            hubContext.Clients.All.operationalHoursUpdated(New With {
                .targetPage = page,
                .operationalHours = operationalHoursForSignalR,
                .timestamp = DateTime.Now
            })

            Return Json(New With {
            .success = True,
            .message = "Operational hours updated and broadcasted to all clients",
            .page = page,
            .ophours = operationalHoursForSignalR
        })

        Catch ex As Exception
            Debug.WriteLine("Error in UpdateOperationalHours: " & ex.Message)
            Return Json(New With {
            .success = False,
            .message = ex.Message
        })
        End Try
    End Function
End Class