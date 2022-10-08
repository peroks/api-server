<?php namespace Peroks\ApiServer\Models;

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
 * @property RequestHandlerInterface $handler The endpoint request handler.
 *
 * @author Per Egil Roksvaag
 * @copyright Per Egil Roksvaag
 * @license MIT License
 */
class Endpoint extends Model {

	/**
	 * @var array An array of model properties.
	 */
	protected static array $properties = [
		'name'    => [
			'id'       => 'name',
			'name'     => 'Endpoint name',
			'desc'     => 'The endpoint name',
			'type'     => PropertyType::STRING,
			'required' => true,
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
			'default'  => Method::GET,
			'enum'     => [
				Method::GET,
				Method::POST,
				Method::PATCH,
				Method::PUT,
				Method::DELETE,
			],
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
