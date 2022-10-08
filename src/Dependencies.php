<?php namespace Peroks\ApiServer;

use Peroks\ApiServer\Exceptions\NotFoundException;
use Psr\Container\ContainerInterface;

/**
 * A PSR-11 container for dependency injection.
 *
 * @author Per Egil Roksvaag
 * @copyright Per Egil Roksvaag
 * @license MIT License
 */
class Dependencies implements ContainerInterface {

	/**
	 * @var Settings The api server settings.
	 */
	protected Settings $settings;

	/**
	 * @var string[] A list of supported identifiers.
	 */
	protected array $identifiers = [ 'plugins' ];

	/**
	 * @param Settings $settings The server settings.
	 */
	public function __construct( Settings $settings ) {
		$this->settings = $settings;
	}

	/**
	 * Finds an entry of the container by its identifier and returns it.
	 *
	 * @param string $id Identifier of the entry to look for.
	 *
	 * @return mixed Entry.
	 */
	public function get( string $id ) {
		if ( 'plugins' === $id ) {
			return $this->settings->plugins;
		}

		$error = sprintf( 'Dependency identifier %s as not found', $id );
		throw new NotFoundException( $error, 500 );
	}

	/**
	 * Returns true if the container can return an entry for the given identifier.
	 * Returns false otherwise.
	 *
	 * @param string $id Identifier of the entry to look for.
	 *
	 * @return bool True if the id is supported, false otherwise.
	 */
	public function has( string $id ): bool {
		return in_array( $id, $this->identifiers, true );
	}
}
