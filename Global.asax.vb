Imports System.Web.Mvc
Imports System.Web.Routing
Imports Microsoft.AspNet.SignalR

Namespace POPB
    Public Class MvcApplication
        Inherits System.Web.HttpApplication

        Sub Application_Start()
            AreaRegistration.RegisterAllAreas()
            GlobalConfiguration.Configure(AddressOf WebApiConfig.Register)
            FilterConfig.RegisterGlobalFilters(GlobalFilters.Filters)
            RouteConfig.RegisterRoutes(RouteTable.Routes)
            
            ' Configure SignalR
            ConfigureSignalR()
        End Sub

        Private Sub ConfigureSignalR()
            ' Enable SignalR
            RouteTable.Routes.MapHubs()
            
            ' Configure JSON serializer for SignalR
            GlobalHost.DependencyResolver.Register(GetType(Newtonsoft.Json.JsonSerializer), Function() New Newtonsoft.Json.JsonSerializer())
        End Sub
    End Class
End Namespace