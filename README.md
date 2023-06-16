# Peroks REST API Server

### An ultra-light REST API server based on PSR-7, PSR-14 and PSR-15 best practice standards.

The REST API server is not a stand-alone application, but a **host** for your
PSR-15 server request handlers and middleware. You can use the server as a module
in your own application or extend it to create custom api servers.

## How to use

### Create a new REST API server instance

    use Peroks\ApiServer\Server;
    $server = new Server();

### Handle server requests

The REST API server itself only provides one single method: It takes a 
[PSR-7 Server Request](https://www.php-fig.org/psr/psr-7/#321-psrhttpmessageserverrequestinterface) and returns a 
[PSR-7 Response](https://www.php-fig.org/psr/psr-7/#33-psrhttpmessageresponseinterface).

    use Peroks\ApiServer\Server;
    use GuzzleHttp\Psr7\ServerRequest;

    $server   = new Server();
	$request  = new ServerRequest( 'POST', '/test', [], 'Hello World' );
	$response = $server->handle( $request );

This server does not process any requests by itself, it just distributes
them to the registered request handlers and middleware and returns their
responses.

### Register API endpoints

In order to register an API endpoint, you first need to create your own
[PSR-15 Server Request Handler](https://www.php-fig.org/psr/psr-15/#11-request-handlers).
You'll find an example of a very simple implementation for testing purposes
here: [TestHandler.php](tests/TestHandler.php).

Use an [Endpoint](src/Endpoint.php) instance to provide the
endpoint **route** (server path) and **http method** in addition to your
PSR-15 server request handler.

The `route` and `action` properties are added to the request as
reserved request **attributes**: `__route` and `__action`.
The `handler` may use these attributes to map server requests
to functions.

    use Peroks\ApiServer\Server;
    use Peroks\ApiServer\Endpoint;

    $server  = new Server();
    $handler = new YourRequestHandler();

    $server->registry->addEndpoint( new Endpoint( [
        'route'   => '/test',           // Endpoint route (server path)
        'method'  => Endpoint::POST,    // Endpoint http method
        'action'  => 'echo',            // Endpoint action
        'handler' => $handler,          // PSR-15 server request handler
    ] ) );

You can check if an endpoint is registered, get it and remove it.

	if( $server->registry->hasEndpoint( '/test', 'POST' ) ) {
        $endpoint = $server->registry->getEndpoint( '/test', 'POST' );
	    $endpoint = $server->registry->removeEndpoint( '/test', 'POST' );
    }

You can also get an array of all registered endpoints.

    $array = $server->registry->getEndpoints();

### Register server middleware

Server middleware is used to modify or monitor server requests or responses.
Typical examples are middleware for **authorisation**, **logging** and **caching**.

In order to register a server middleware, you first need to create your own
[PSR-15 Server Middleware](https://www.php-fig.org/psr/psr-15/#12-middleware).
You'll find an example of a very simple implementation for testing purposes
here: [TestMiddleware.php](tests/TestMiddleware.php).

Use a [Middleware](src/Middleware.php) wrapper to provide the middleware `id`
and your PSR-15 server middleware `instance` (both are required).
The `id` can be any string identifying the registered middleware entry.
In many cases, the middleware class name is a good choice.

    use Peroks\ApiServer\Server;
    use Peroks\ApiServer\Middleware;

    $server     = new Server();
    $middleware = new YourMiddleware();

    $server->registry->addMiddleware( new Middleware( [
        'id'       => YourMiddleware::class,
        'name'     => 'Middleware instance for testing',
        'priority' => 20, // default is 50.
        'instance' => $middleware,
    ] ) );

You'll need the registered `id` if you later want to check if the middleware is
registered, and to get or remove the middleware, i.e. like this:

	if( $server->registry->hasMiddleware( YourMiddleware::class ) ) {
        $middleware = $server->registry->getMiddleware( YourMiddleware::class );
	    $middleware = $server->registry->removeMiddleware( YourMiddleware::class );
    }

You can also get an array of all registered middleware entries.

    $array = $server->registry->getMiddlewareEntries();

### Register event listeners

In addition to middleware, you can also register **event listeners** to hook
into the execution flow and modify data.

Use a [Listener](src/Listener.php) wrapper to provide an `id`,
`type` and `callback` function for the listener (all are required).
The `id` can be any string identifying the registered event listener.

In this example, we add authorization headers to all requests before they are
handled and possibly rejected by a middleware or request handler.

    use Peroks\ApiServer\Server;
    use Peroks\ApiServer\Listener;
    use Peroks\ApiServer\Event;

    $server   = new Server();
    $callback = function( Event $event ): void {
        $event->data->request = $event->data->request->withHeader( 'authorization', 'yes' );
    };

    $server->registry->addListener( new Listener( [
        'id'       => 'set-authorization',
        'type'     => 'server/request',
        'callback' => $callback,
    ] ) );

You'll need the registered `id` if you later want to check if the event listener
is registered, and to get or remove the listener, i.e. like this:

	if( $server->registry->hasListener( 'set-authorization' ) ) {
        $listener = $server->registry->getListener( 'set-authorization' );
	    $listener = $server->registry->removeListener( 'set-authorization' );
    }

You can also get an array of all registered event listeners.

    $array = $server->registry->getListeners();
