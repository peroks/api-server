# Peroks REST API Server

### An ultra-light REST API server based on PSR-7, PSR-14 and PSR-15 best practice standards.

The REST API server is not a stand-alone application, but a host for external
PSR-15 request handlers and middleware. You can use this class as a module
in your own application or extend it to create custom api servers.

This server does not handle any requests by itself, it just dispatches
them to the registered request handlers and middleware and returns their
responses.

## How to use

### Create a new REST API server instance

    use Peroks\ApiServer\Server;
    $server = new Server();

### Register an API endpoint

In order to register an API endpoint, you first need to create your own
[PSR-15 Server Request Handler](https://www.php-fig.org/psr/psr-15/) implementation.
Use an [Endpoint](src/Endpoint.php) instance to provide the
endpoint **route** (server path) and **http method** in addition to your
PSR-15 server request handler. The `id` is forwarded to the `handler` to
identify the endpoint.

You'll find an example of a very simple handler implementation here:
[TestHandler.php](tests/TestHandler.php) 

    use Peroks\ApiServer\Server;
    use Peroks\ApiServer\Endpoint;

    $server  = new Server();
    $handler = new YourRequestHandler();

    $server->registry->addEndpoint( new Endpoint( [
        'id'      => 'echo',            // Endpoint id
        'route'   => '/test',           // Endpoint route
        'method'  => Endpoint::POST,    // Endpoint method
        'handler' => $handler,          // PSR-15 server request handler
    ] ) );

You can check if an endpoint is registered, get it and remove it.

	if( $server->registry->hasEndpoint( route: '/test, method: 'POST' ) ) {
        $endpoint = $server->registry->getEndpoint( route: '/test, method: 'POST' );
	    $endpoint = $server->registry->removeEndpoint( route: '/test, method: 'POST' );
    }

You can also get an array of all registered endpoints.

    $array = $server->registry->getEndpoints();

### Handle a server request

    use Peroks\ApiServer\Server;
    use GuzzleHttp\Psr7\ServerRequest;

    $server   = new Server();
	$request  = new ServerRequest( 'POST', '/test', [], 'Hello World' );
	$response = $server->handle( $request );

### Register an Server middleware
