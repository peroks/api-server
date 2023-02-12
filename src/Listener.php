<?php namespace Peroks\ApiServer;

use Peroks\Model\Model;
use Peroks\Model\PropertyType;

/**
 * An event listener entry.
 *
 * @property string $id The unique listener id.
 * @property string $name The listener name.
 * @property string $desc The listener description.
 * @property string $type The listener type.
 * @property string $priority The listener priority.
 * @property string $callback The listener callback.
 *
 * @author Per Egil Roksvaag
 * @copyright Per Egil Roksvaag
 * @license MIT License
 */
class Listener extends Model {

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
			'name'     => 'Listener id',
			'desc'     => 'The listener id, must be unique for each handler',
			'type'     => PropertyType::STRING,
			'required' => true,
		],
		'name'     => [
			'id'       => 'name',
			'name'     => 'Listener name',
			'desc'     => 'The listener name',
			'type'     => PropertyType::STRING,
			'required' => false,
		],
		'desc'     => [
			'id'       => 'desc',
			'name'     => 'Listener description',
			'desc'     => 'The listener description',
			'type'     => PropertyType::STRING,
			'required' => false,
		],
		'type'     => [
			'id'       => 'type',
			'name'     => 'Listener type',
			'desc'     => 'The listener type',
			'type'     => PropertyType::STRING,
			'required' => true,
		],
		'priority' => [
			'id'       => 'priority',
			'name'     => 'Listener priority',
			'desc'     => 'The listener priority',
			'type'     => PropertyType::INTEGER,
			'required' => true,
			'default'  => 50,
			'min'      => 1,
			'max'      => 99,
		],
		'callback' => [
			'id'       => 'callback',
			'name'     => 'Listener callback',
			'desc'     => 'The listener callback',
			'type'     => PropertyType::FUNCTION,
			'required' => true,
		],
	];
}
