<?php
/**
 * A PSR-15 middleware entry.
 *
 * @author Per Egil Roksvaag
 * @copyright Per Egil Roksvaag
 * @license MIT License
 */

declare( strict_types = 1 );
namespace Peroks\ApiServer;

use Peroks\Model\Model;
use Peroks\Model\PropertyType;
use Psr\Http\Server\MiddlewareInterface;

/**
 * A PSR-15 middleware entry.
 *
 * @property string $id The middleware id.
 * @property string $name The middleware name.
 * @property string $desc The middleware description.
 * @property integer $priority The middleware priority.
 * @property MiddlewareInterface $instance A PSR-15 middleware instance.
 */
class Middleware extends Model {

	/**
	 * @var string The model's id property.
	 */
	protected static string $idProperty = 'id';

	/**
	 * @var array An array of model properties.
	 */
	protected static array $properties = [
		'id'       => [
			'id'       => 'id',
			'name'     => 'Middleware id',
			'desc'     => 'The middleware unique id',
			'type'     => PropertyType::STRING,
			'required' => true,
		],
		'name'     => [
			'id'       => 'name',
			'name'     => 'Middleware name',
			'desc'     => 'The middleware name',
			'type'     => PropertyType::STRING,
			'required' => false,
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
