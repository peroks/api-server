# peroks/api-server

### An ultra light api server based on PSR-7, PSR-14 and PSR-15 best practice standards.

The api server is not a stand-alone application, but a host for external
PSR-15 request handlers and middleware. You can use this class as a module
in your own application or extend it to create custom api servers.

The api server does not handle any requests by itself, it just dispatches
them to the registered request handlers and middleware and returns their
responses.

