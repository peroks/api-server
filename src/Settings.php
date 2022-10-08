<?php namespace Peroks\ApiServer;

use Peroks\Model\Model;
use Peroks\Model\PropertyType;

/**
 * Server settings.
 *
 * @property array plugins An array of server plugins.
 *
 * @author Per Egil Roksvaag
 * @copyright Per Egil Roksvaag
 * @license MIT License
 */
class Settings extends Model {

	/**
	 * @var array An array of model properties.
	 */
	protected static array $properties = [
		'plugins' => [
			'id'       => 'plugins',
			'name'     => 'Server plugins',
			'desc'     => 'An array of full paths to server plugins',
			'type'     => PropertyType::ARRAY,
			'required' => true,
			'default'  => [],
		],
	];

	/**
	 * Reads settings from a json configuration file.
	 *
	 * @param string $file The full path to a json configuration file.
	 * @param array $data Additional configuration settings.
	 *
	 * @return static
	 */
	public static function read( string $file, array $data = [] ): self {
		if ( $file && is_readable( $file ) ) {
			$content    = file_get_contents( $file );
			$connection = json_decode( $content, true, 64, JSON_THROW_ON_ERROR );
			$data       = array_replace( $connection, $data );
		}

		return static::create( $data )->validate( true );
	}
}
