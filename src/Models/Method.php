<?php namespace Peroks\ApiServer\Models;

/**
 * Supported http methods.
 *
 * @author Per Egil Roksvaag
 * @copyright Per Egil Roksvaag
 * @license MIT License
 */
abstract class Method {
	const GET    = 'GET';
	const POST   = 'POST';
	const PUT    = 'PUT';
	const PATCH  = 'PATCH';
	const DELETE = 'DELETE';
}
