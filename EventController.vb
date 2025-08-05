Imports System.Data.Entity
Imports System.Net
Imports Newtonsoft.Json
Imports Microsoft.AspNet.SignalR

Namespace Controllers
    Public Class EventController
        Inherits System.Web.Mvc.Controller

        Private db As New POPB
        Private hubContext As IHubContext = GlobalHost.ConnectionManager.GetHubContext(Of EventHub)()

        ' ... existing code ...

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

                ' Determine target page based on event_held value
                Dim targetPage As String = "jumat" ' Default
                If Not String.IsNullOrEmpty(selectedEvent.event_held) Then
                    If selectedEvent.event_held.ToLower().Contains("thursday") OrElse selectedEvent.event_held.ToLower().Contains("kamis") Then
                        targetPage = "kamis"
                    End If
                End If

                ' Get operational hours based on the selected targetPage
                Dim operationalHours = db.POPB_opday.Where(Function(op) op.page.ToLower() = targetPage).ToList()

                ' Create broadcast data
                Dim broadcastData = New With {
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

                ' Broadcast to all connected clients
                hubContext.Clients.All.eventSelected(broadcastData)

                ' Debugging output to check the values
                Debug.WriteLine("Event Selected Successfully: ID=" & selectedEvent.id & ", Time=" & selectedEvent.event_time & ", Held=" & selectedEvent.event_held)

                ' Set success message
                TempData("SuccessMessage") = "Event with time'" & selectedEvent.event_time & "' selected successfully"

                ' Redirect to the Index action to show updated view
                Return RedirectToAction("Index")

            Catch ex As Exception
                Debug.WriteLine("Error in SelectEvent: " & ex.Message)
                Debug.WriteLine("Stack trace: " & ex.StackTrace)
                TempData("ErrorMessage") = "An error occurred while selecting the event"
                Return RedirectToAction("Index")
            End Try
        End Function

        ' POST: EventController/SelectEventDetails - AJAX endpoint with real-time broadcast
        <HttpPost>
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

                ' Broadcast to all connected clients
                hubContext.Clients.All.eventSelected(response)

                Debug.WriteLine("Response JSON: " & JsonConvert.SerializeObject(response))

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

        ' ... existing code ...
    End Class
End Namespace