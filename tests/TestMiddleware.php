<?php namespace Peroks\ApiServer\Tests;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Middleware class for testing purposes.
 *
 * @author Per Egil Roksvaag
 * @copyright Per Egil Roksvaag
 * @license MIT License
 */
class TestMiddleware implements MiddlewareInterface {
	public function process( ServerRequestInterface $request, RequestHandlerInterface $handler ): ResponseInterface {
		return $handler->handle( $request );
	}
}
