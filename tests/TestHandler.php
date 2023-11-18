<?php declare( strict_types = 1 ); namespace Peroks\ApiServer\Tests;

use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 *  PSR-15 Server Request Handler implementation for testing purposes.
 *
 * @author Per Egil Roksvaag
 * @copyright Per Egil Roksvaag
 * @license MIT License
 */
class TestHandler implements RequestHandlerInterface {

	/**
	 * Handles product requests from the admin user interface.
	 *
	 * @param ServerRequestInterface $request A PSR-7 server request.
	 *
	 * @return ResponseInterface A PSR-7 response.
	 */
	public function handle( ServerRequestInterface $request ): ResponseInterface {
		$action = $request->getAttribute( '__action' );
		return call_user_func( [ $this, $action ], $request );
	}

	/**
	 * Returns a list of products.
	 *
	 * Handles the GET /admin/products request.
	 *
	 * @return ResponseInterface A PSR-7 response.
	 */
	public function hello(): ResponseInterface {
		return new Response( body: 'Hello World' );
	}

	/**
	 * @param ServerRequestInterface $request A PSR-7 server request.
	 *
	 * @return ResponseInterface A PSR-7 response.
	 */
	public function echo( ServerRequestInterface $request ): ResponseInterface {
		return new Response( body: $request->getBody() );
	}
}
