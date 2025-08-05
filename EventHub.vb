Imports Microsoft.AspNet.SignalR
Imports System.Threading.Tasks

Public Class EventHub
    Inherits Hub

    ' Group name for event updates
    Private Const EVENT_GROUP As String = "EventUpdates"

    ' Method called when a client connects
    Public Overrides Function OnConnected() As Task
        ' Add the client to the event updates group
        Groups.Add(Context.ConnectionId, EVENT_GROUP)
        Debug.WriteLine($"Client connected: {Context.ConnectionId}")
        Return MyBase.OnConnected()
    End Function

    ' Method called when a client disconnects
    Public Overrides Function OnDisconnected(stopCalled As Boolean) As Task
        ' Remove the client from the event updates group
        Groups.Remove(Context.ConnectionId, EVENT_GROUP)
        Debug.WriteLine($"Client disconnected: {Context.ConnectionId}")
        Return MyBase.OnDisconnected(stopCalled)
    End Function

    ' Method to notify all clients about event selection
    Public Sub NotifyEventSelected(eventId As Integer, eventTime As String, eventHeld As String, targetPage As String)
        Try
            Debug.WriteLine($"Notifying clients about event selection: ID={eventId}, Time={eventTime}")
            
            ' Broadcast to all clients in the group
            Clients.Group(EVENT_GROUP).eventSelected(New With {
                .eventId = eventId,
                .eventTime = eventTime,
                .eventHeld = eventHeld,
                .targetPage = targetPage,
                .timestamp = DateTime.Now
            })
        Catch ex As Exception
            Debug.WriteLine($"Error in NotifyEventSelected: {ex.Message}")
        End Try
    End Sub

    ' Method to notify all clients about event clearing
    Public Sub NotifyEventCleared()
        Try
            Debug.WriteLine("Notifying clients about event clearing")
            
            ' Broadcast to all clients in the group
            Clients.Group(EVENT_GROUP).eventCleared(New With {
                .timestamp = DateTime.Now
            })
        Catch ex As Exception
            Debug.WriteLine($"Error in NotifyEventCleared: {ex.Message}")
        End Try
    End Sub

    ' Method to notify all clients about operational hours update
    Public Sub NotifyOperationalHoursUpdate(targetPage As String, operationalHours As List(Of Object))
        Try
            Debug.WriteLine($"Notifying clients about operational hours update for page: {targetPage}")
            
            ' Broadcast to all clients in the group
            Clients.Group(EVENT_GROUP).operationalHoursUpdated(New With {
                .targetPage = targetPage,
                .operationalHours = operationalHours,
                .timestamp = DateTime.Now
            })
        Catch ex As Exception
            Debug.WriteLine($"Error in NotifyOperationalHoursUpdate: {ex.Message}")
        End Try
    End Sub

    ' Method for clients to join specific event groups
    Public Sub JoinEventGroup(eventId As Integer)
        Try
            Dim groupName As String = $"Event_{eventId}"
            Groups.Add(Context.ConnectionId, groupName)
            Debug.WriteLine($"Client {Context.ConnectionId} joined event group: {groupName}")
        Catch ex As Exception
            Debug.WriteLine($"Error in JoinEventGroup: {ex.Message}")
        End Try
    End Sub

    ' Method for clients to leave specific event groups
    Public Sub LeaveEventGroup(eventId As Integer)
        Try
            Dim groupName As String = $"Event_{eventId}"
            Groups.Remove(Context.ConnectionId, groupName)
            Debug.WriteLine($"Client {Context.ConnectionId} left event group: {groupName}")
        Catch ex As Exception
            Debug.WriteLine($"Error in LeaveEventGroup: {ex.Message}")
        End Try
    End Sub
End Class