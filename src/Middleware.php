<?php namespace Peroks\ApiServer;

use Peroks\Model\Model;
use Peroks\Model\PropertyType;
use Psr\Http\Server\MiddlewareInterface;

/**
 * A PSR-15 middleware entry.
 *
 * @property string $name The middleware name.
 * @property string $desc The middleware description.
 * @property integer $priority The middleware priority.
 * @property MiddlewareInterface $instance A PSR-15 middleware instance.
 *
 * @author Per Egil Roksvaag
 * @copyright Per Egil Roksvaag
 * @license MIT License
 */
class Middleware extends Model {

	/**
	 * @var array An array of model properties.
	 */
	protected static array $properties = [
		'name'     => [
			'id'       => 'name',
			'name'     => 'Middleware name',
			'desc'     => 'The middleware name',
			'type'     => PropertyType::STRING,
			'required' => true,
		],
		'desc'     => [
			'id'       => 'desc',
			'name'     => 'Middleware description',
			'desc'     => 'The middleware description',
			'type'     => PropertyType::STRING,
			'required' => false,
		],
		'priority' => [
			'id'       => 'priority',
			'name'     => 'Middleware priority',
			'desc'     => 'The middleware priority',
			'type'     => PropertyType::INTEGER,
			'required' => true,
			'default'  => 50,
			'min'      => 1,
			'max'      => 99,
		],
		'instance' => [
			'id'       => 'instance',
			'name'     => 'Middleware instance',
			'desc'     => 'A PSR-15 middleware instance',
			'type'     => PropertyType::OBJECT,
			'object'   => MiddlewareInterface::class,
			'required' => true,
		],
	];
}