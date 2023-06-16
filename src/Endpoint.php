<?php namespace Peroks\ApiServer;

use Peroks\Model\Model;
use Peroks\Model\PropertyType;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * A server request endpoint.
 *
 * @property string $name The endpoint name.
 * @property string $desc The endpoint description.
 * @property string $route The endpoint route.
 * @property string $method The endpoint method.
 * @property string $action The endpoint action, can reflect handler function names.
 * @property RequestHandlerInterface $handler A PSR-15 request handler.
 *
 * @author Per Egil Roksvaag
 * @copyright Per Egil Roksvaag
 * @license MIT License
 */
class Endpoint extends Model {

	// Supported http methods.
	const GET     = 'GET';
	const HEAD    = 'HEAD';
	const POST    = 'POST';
	const PUT     = 'PUT';
	const PATCH   = 'PATCH';
	const DELETE  = 'DELETE';
	const CONNECT = 'CONNECT';
	const OPTIONS = 'OPTIONS';
	const TRACE   = 'TRACE';

	/**
	 * @var array An array of model properties.
	 */
	protected static array $properties = [
		'name'    => [
			'id'       => 'name',
			'name'     => 'Endpoint name',
			'desc'     => 'The endpoint name',
			'type'     => PropertyType::STRING,
			'required' => false,
		],
		'desc'    => [
			'id'       => 'desc',
			'name'     => 'Endpoint description',
			'desc'     => 'The endpoint description',
			'type'     => PropertyType::STRING,
			'required' => false,
		],
		'route'   => [
			'id'       => 'route',
			'name'     => 'Endpoint route',
			'desc'     => 'The endpoint route',
			'type'     => PropertyType::STRING,
			'required' => true,
		],
		'method'  => [
			'id'       => 'method',
			'name'     => 'Endpoint method',
			'desc'     => 'The endpoint method',
			'type'     => PropertyType::STRING,
			'required' => true,
			'default'  => self::GET,
			'enum'     => [
				self::GET,
				self::HEAD,
				self::POST,
				self::PUT,
				self::PATCH,
				self::DELETE,
				self::CONNECT,
				self::OPTIONS,
				self::TRACE,
			],
		],
		'action'  => [
			'id'       => 'action',
			'name'     => 'Endpoint action',
			'desc'     => 'The endpoint action',
			'type'     => PropertyType::STRING,
			'required' => false,
		],
		'handler' => [
			'id'       => 'handler',
			'name'     => 'Endpoint handler',
			'desc'     => 'The endpoint request handler',
			'type'     => PropertyType::OBJECT,
			'object'   => RequestHandlerInterface::class,
			'required' => true,
		],
	];
}
