<?php namespace Peroks\ApiServer\Tests;

use JsonException;
use Peroks\ApiServer\Endpoint;
use Peroks\Model\ModelData;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Silverscreen\Octopus\Core\Models\Product;
use Silverscreen\Octopus\Core\Octopus;
use Silverscreen\Octopus\Core\Registry;
use Silverscreen\Octopus\Core\Response;
use Silverscreen\Octopus\Core\Utils;

/**
 * Handler class for testing purposes.
 *
 * @author Per Egil Roksvaag
 * @copyright Per Egil Roksvaag
 * @license MIT License
 */
class TestHandler implements RequestHandlerInterface {

	/**
	 * @var Octopus The Octopus core application.
	 */
	protected Octopus $octopus;

	/**
	 * Constructor.
	 *
	 * @param Octopus $octopus The Octopus core application.
	 */
	public function __construct( Octopus $octopus ) {
		$this->octopus = $octopus;
		$this->register( $octopus->registry );
	}

	/**
	 * Adds request endpoints for products to the Octopus registry.
	 *
	 * @param Registry $registry The Octopus registry.
	 */
	protected function register( Registry $registry ): void {
		$registry->addEndpoint( new Endpoint( [
			'id'      => 'getProducts',
			'route'   => '/admin/products',
			'method'  => Endpoint::GET,
			'handler' => $this,
		] ) );

		$registry->addEndpoint( new Endpoint( [
			'id'      => 'addProducts',
			'route'   => '/admin/products',
			'method'  => Endpoint::POST,
			'handler' => $this,
		] ) );

		$registry->addEndpoint( new Endpoint( [
			'id'      => 'getProduct',
			'route'   => '/admin/products/(?P<productId>[\w-]+)',
			'method'  => Endpoint::GET,
			'handler' => $this,
		] ) );

		$registry->addEndpoint( new Endpoint( [
			'id'      => 'setProduct',
			'route'   => '/admin/products/(?P<productId>[\w-]+)',
			'method'  => Endpoint::PUT,
			'handler' => $this,
		] ) );

		$registry->addEndpoint( new Endpoint( [
			'id'      => 'deleteProduct',
			'route'   => '/admin/products/(?P<productId>[\w-]+)',
			'method'  => Endpoint::DELETE,
			'handler' => $this,
		] ) );

		$registry->addEndpoint( new Endpoint( [
			'id'      => 'editProductProperties',
			'route'   => '/admin/products/(?P<productId>[\w-]+)/properties',
			'method'  => Endpoint::GET,
			'handler' => $this,
		] ) );
	}

	/**
	 * Handles product requests from the admin user interface.
	 *
	 * @param ServerRequestInterface $request A PSR-7 server request.
	 *
	 * @return ResponseInterface A PSR-7 response.
	 */
	public function handle( ServerRequestInterface $request ): ResponseInterface {
		$id = $request->getAttribute( '_id' );
		return call_user_func( [ $this, $id ], $request );
	}

	/**
	 * Returns a list of products.
	 *
	 * Handles the GET /admin/products request.
	 *
	 * @return ResponseInterface A PSR-7 response.
	 */
	public function getProducts(): ResponseInterface {
		$result = $this->octopus->store->getProducts();

		usort( $result, function( Product $a, Product $b ) {
			return $a->name <=> $b->name;
		} );

		return Response::create( array_values( $result ) );
	}

	/**
	 * @param ServerRequestInterface $request A PSR-7 server request.
	 *
	 * @return ResponseInterface A PSR-7 response.
	 * @throws JsonException
	 */
	public function addProducts( ServerRequestInterface $request ): ResponseInterface {
		$connection   = Utils::decode( $request, false );
		$providerInfo = $this->octopus->registry->getProvider( $connection->provider );
		$template     = Product::create( [
			'providerId'   => $provider::info()->id,
			'providerName' => $provider::info()->name,
			'connection'   => $connection,
		] );

		$response = $provider->getProducts( $request );

		foreach ( Utils::decode( $response ) as $data ) {
			$result[] = $product = Product::create( $data )->patch( $template->data() );
			$this->octopus->store->setProduct( $product );
		}
		return Response::create( $result ?? [] );
	}

	/**
	 * Returns a list of products.
	 *
	 * Handles the GET /admin/products request.
	 *
	 * @param ServerRequestInterface $request A PSR-7 server request.
	 *
	 * @return ResponseInterface A PSR-7 response.
	 */
	public function getProduct( ServerRequestInterface $request ): ResponseInterface {
		$productId = $request->getAttribute( 'productId' );
		$product   = $this->octopus->store->getProduct( $productId );

		if ( $product ) {
			return Response::create( new Product( [
				'id'   => $product->id,
				'name' => $product->name,
			] ) );
		}

		$message = sprintf( 'Product %s not found', $productId );
		return Response::create( $message, 404 );
	}

	/**
	 * @param ServerRequestInterface $request A PSR-7 server request.
	 *
	 * @return ResponseInterface A PSR-7 response.
	 * @throws JsonException
	 */
	public function setProduct( ServerRequestInterface $request ): ResponseInterface {
		$data      = Utils::decode( $request );
		$data      = Utils::parseFormData( $data );
		$product   = new Product( $data );
		$productId = $this->octopus->store->setProduct( $product );

		if ( $productId ) {
			return Response::create( $product );
		}

		$message = sprintf( 'Product %s not found', $productId );
		return Response::create( $message, 404 );
	}

	/**
	 * @param ServerRequestInterface $request A PSR-7 server request.
	 *
	 * @return ResponseInterface A PSR-7 response.
	 */
	public function deleteProduct( ServerRequestInterface $request ): ResponseInterface {
		$productId = $request->getAttribute( 'productId' );
		$result    = $this->octopus->store->deleteProduct( $productId );

		return Response::create( $result );
	}

	/**
	 * Returns a list of products.
	 *
	 * Handles the GET /admin/products request.
	 *
	 * @param ServerRequestInterface $request A PSR-7 server request.
	 *
	 * @return ResponseInterface A PSR-7 response.
	 */
	public function editProductProperties( ServerRequestInterface $request ): ResponseInterface {
		$productId = $request->getAttribute( 'productId' );
		$product   = $this->octopus->store->getProduct( $productId );

		return Response::create( $product->data( ModelData::PROPERTIES ) );
	}
}
