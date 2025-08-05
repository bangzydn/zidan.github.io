Imports Microsoft.AspNet.SignalR

Namespace Hubs
    Public Class EventHub
        Inherits Hub

        Public Sub JoinEventGroup()
            ' Join a group for event updates
            Groups.Add(Context.ConnectionId, "EventUpdates")
        End Sub

        Public Sub LeaveEventGroup()
            ' Leave the event updates group
            Groups.Remove(Context.ConnectionId, "EventUpdates")
        End Sub

        Public Sub SendEventUpdate(eventData As Object)
            ' Broadcast event update to all clients in the group
            Clients.Group("EventUpdates").eventSelected(eventData)
        End Sub

        Public Overrides Function OnConnected() As Task
            ' Automatically join the event group when connected
            JoinEventGroup()
            Return MyBase.OnConnected()
        End Function

        Public Overrides Function OnDisconnected() As Task
            ' Leave the event group when disconnected
            LeaveEventGroup()
            Return MyBase.OnDisconnected()
        End Function
    End Class
End Namespace