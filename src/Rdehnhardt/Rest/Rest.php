<?php

namespace Rdehnhardt\Rest;

use Aws\CloudFront\Exception\Exception;
use Rdehnhardt\Curl\Curl;
class Rest {
	
	/**
	 *
	 * @var unknown
	 */
	protected $rest_server;
	
	/**
	 *
	 * @var unknown
	 */
	protected $format;
	
	/**
	 *
	 * @var unknown
	 */
	protected $mime_type;
	
	/**
	 *
	 * @var unknown
	 */
	protected $http_auth = null;
	
	/**
	 *
	 * @var unknown
	 */
	protected $http_user = null;
	
	/**
	 *
	 * @var unknown
	 */
	protected $http_pass = null;
	
	/**
	 *
	 * @var unknown
	 */
	protected $response_string;
	
	/**
	 *
	 * @var unknown
	 */
	protected $key_name = null;
	
	/**
	 *
	 * @var unknown
	 */
	protected $key = null;
	
	/**
	 *
	 * @var obejct
	 */
	protected $curl;
	
	/**
	 *
	 * @var multitype:string
	 */
	protected $supported_formats = array (
		'xml' => 'application/xml',
		'json' => 'application/json',
		'serialize' => 'application/vnd.php.serialized',
		'php' => 'text/plain',
		'csv' => 'text/csv' 
	);
	
	/**
	 *
	 * @var multitype:string
	 */
	protected $auto_detect_formats = array (
		'application/xml' => 'xml',
		'text/xml' => 'xml',
		'application/json' => 'json',
		'text/json' => 'json',
		'text/csv' => 'csv',
		'application/csv' => 'csv',
		'application/vnd.php.serialized' => 'serialize' 
	);
	
	/**
	 * Rest Construct
	 *
	 * @todo Check Curl Initialize
	 * @param array $config        	
	 */
	public function __construct() {
		$config = \Config::get('rest::config');
		
		if ($config) {
			$this->initialize($config);
			
			$this->curl = new Curl();
			
			return $this;
		}
		
		throw new Exception('No config found', 'REST-001');
	}
	
	/**
	 * Destruct Rest
	 */
	public function __destruct() {
		$this->curl->set_defaults ();
	}
	
	/**
	 * 
	 * @param array $config
	 */
	public function initialize($config) {
		$this->rest_server = @$config ['server'];
		
		if (substr ( $this->rest_server, - 1, 1 ) != '/') {
			$this->rest_server .= '/';
		}
		
		( \Config::get('rest::http.auth') ) && $this->http_auth = \Config::get('rest::http.auth');
		( \Config::get('rest::http.user') ) && $this->http_user = \Config::get('rest::http.user');
		( \Config::get('rest::http.pass') ) && $this->http_pass = \Config::get('rest::http.pass');
	}
	
	/**
	 * 
	 * @param unknown $uri
	 * @param unknown $params
	 * @param string $format
	 */
	public function get($uri, $params = array(), $format = NULL) {
		if ($params) {
			$uri .= '?' . (is_array ( $params ) ? http_build_query ( $params ) : $params);
		}
		
		return $this->_call ( 'get', $uri, NULL, $format );
	}
	
	/**
	 * 
	 * @param unknown $uri
	 * @param unknown $params
	 * @param string $format
	 */
	public function post($uri, $params = array(), $format = NULL) {
		return $this->_call ( 'post', $uri, $params, $format );
	}
	
	/**
	 * 
	 * @param unknown $uri
	 * @param unknown $params
	 * @param string $format
	 */
	public function put($uri, $params = array(), $format = NULL) {
		return $this->_call ( 'put', $uri, $params, $format );
	}
	
	/**
	 * 
	 * @param unknown $uri
	 * @param unknown $params
	 * @param string $format
	 */
	public function delete($uri, $params = array(), $format = NULL) {
		return $this->_call ( 'delete', $uri, $params, $format );
	}
	
	/**
	 * 
	 * @param unknown $key
	 * @param string $name
	 */
	public function setKey($key, $name = 'X-API-KEY') {
		$this->key_name = $name;
		$this->key = $key;
	}
	
	/**
	 * 
	 * @param unknown $key
	 * @param string $name
	 */
	public function api_key($key, $name = 'X-API-KEY') {
		$this->curl->http_header ( $name, $key );
	}
	
	/**
	 * 
	 * @param unknown $lang
	 */
	public function language($lang) {
		if (is_array ( $lang )) {
			$lang = implode ( ', ', $lang );
		}
		
		$this->curl->http_header ( 'Accept-Language', $lang );
	}
	
	/**
	 * 
	 * @param unknown $method
	 * @param unknown $uri
	 * @param unknown $params
	 * @param string $format
	 */
	protected function _call($method, $uri, $params = array(), $format = NULL) {
		if ($format !== NULL) {
			$this->format ( $format );
		}
		
		if ($this->key && $this->key_name) {
			$this->api_key ( $this->key, $this->key_name );
		}
		
		$this->http_header ( 'Accept', $this->mime_type );
		$this->curl->create ( $this->rest_server . $uri );
		
		if ($this->http_auth != '' && $this->http_user != '') {
			$this->curl->http_login ( $this->http_user, $this->http_pass, $this->http_auth );
		}
		
		$this->curl->option ( 'failonerror', FALSE );
		$this->curl->{$method} ( $params );
		$response = $this->curl->execute ();
		
		return $this->_format_response ( $response );
	}
	
	/**
	 * If a type is passed in that is not supported, use it as a mime type
	 * 
	 * @param unknown $format
	 * @return \Rdehnhardt\Rest\Rest
	 */
	public function format($format) {
		if (array_key_exists ( $format, $this->supported_formats )) {
			$this->format = $format;
			$this->mime_type = $this->supported_formats [$format];
		} else {
			$this->mime_type = $format;
		}
		
		return $this;
	}
	
	/**
	 * 
	 */
	public function debug() {
		$request = $this->curl->debug_request ();
		
		echo "=============================================<br/>\n";
		echo "<h2>REST Test</h2>\n";
		echo "=============================================<br/>\n";
		echo "<h3>Request</h3>\n";
		echo $request ['url'] . "<br/>\n";
		echo "=============================================<br/>\n";
		echo "<h3>Response</h3>\n";
		
		if ($this->response_string) {
			echo "<code>" . nl2br ( htmlentities ( $this->response_string ) ) . "</code><br/>\n\n";
		} else {
			echo "No response<br/>\n\n";
		}
		
		echo "=============================================<br/>\n";
		
		if ($this->curl->error_string) {
			echo "<h3>Errors</h3>";
			echo "<strong>Code:</strong> " . $this->curl->error_code . "<br/>\n";
			echo "<strong>Message:</strong> " . $this->curl->error_string . "<br/>\n";
			echo "=============================================<br/>\n";
		}
		
		echo "<h3>Call details</h3>";
		echo "<pre>";
		print_r ( $this->curl->info );
		echo "</pre>";
		die ();
	}
	
	/**
	 * Return HTTP status code
	 */
	public function status() {
		return $this->info ( 'http_code' );
	}
	
	/**
	 * Return curl info by specified key, or whole array
	 * 
	 * @param string $key
	 */
	public function info($key = null) {
		return $key === null ? $this->curl->info : @$this->curl->info [$key];
	}
	
	/**
	 * Set custom options
	 * 
	 * @param unknown $code
	 * @param unknown $value
	 */
	public function option($code, $value) {
		$this->curl->option ( $code, $value );
	}
	
	/**
	 * 
	 * @param unknown $header
	 * @param string $content
	 */
	public function http_header($header, $content = NULL) {
		$params = $content ? array ($header,$content ) : array ($header);
		
		call_user_func_array ( array ($this->curl,'http_header' ), $params );
	}
	
	/**
	 * 
	 * @param unknown $response
	 * @return unknown
	 */
	protected function _format_response($response) {
		$this->response_string = & $response;
		
		if (array_key_exists ( $this->format, $this->supported_formats )) {
			return $this->{"_" . $this->format} ( $response );
		}
		
		$returned_mime = @$this->curl->info ['content_type'];
		
		if (strpos ( $returned_mime, ';' )) {
			list ( $returned_mime ) = explode ( ';', $returned_mime );
		}
		
		$returned_mime = trim ( $returned_mime );
		
		if (array_key_exists ( $returned_mime, $this->auto_detect_formats )) {
			return $this->{'_' . $this->auto_detect_formats [$returned_mime]} ( $response );
		}
		
		return $response;
	}
	
	/**
	 * Format XML for output
	 *  
	 * @param unknown $string
	 * @return Ambigous <array, multitype:>
	 */
	protected function _xml($string) {
		return $string ? ( array ) simplexml_load_string ( $string, 'SimpleXMLElement', LIBXML_NOCDATA ) : array ();
	}
	
	/**
	 * Format HTML for output
	 * This function is DODGY! Not perfect CSV support but works with my REST_Controller
	 * 
	 * @param unknown $string
	 * @return multitype:multitype:
	 */
	protected function _csv($string) {
		$data = array ();
		
		$rows = explode ( "\n", trim ( $string ) );
		$headings = explode ( ',', array_shift ( $rows ) );
		foreach ( $rows as $row ) {
			$data_fields = explode ( '","', trim ( substr ( $row, 1, - 1 ) ) );
			
			if (count ( $data_fields ) === count ( $headings )) {
				$data [] = array_combine ( $headings, $data_fields );
			}
		}
		
		return $data;
	}
	
	/**
	 * Encode as JSON
	 * 
	 * @param unknown $string
	 * @return json
	 */
	protected function _json($string) {
		return json_decode ( trim ( $string ) );
	}
	
	/**
	 * Encode as Serialized array
	 * 
	 * @param unknown $string
	 * @return mixed
	 */
	protected function _serialize($string) {
		return unserialize ( trim ( $string ) );
	}
	
	/**
	 * Encode raw PHP
	 * 
	 * @param unknown $string
	 * @return multitype:
	 */
	protected function _php($string) {
		$string = trim ( $string );
		$populated = array ();
		eval ( "\$populated = \"$string\";" );
		return $populated;
	}
}