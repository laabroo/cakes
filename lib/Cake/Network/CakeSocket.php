<?php
/**
 * Cake Socket connection class.
 *
 * PHP 5
 *
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright 2005-2012, Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright 2005-2012, Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://cakephp.org CakePHP(tm) Project
 * @package       Cake.Network
 * @since         CakePHP(tm) v 1.2.0
 * @license       MIT License (http://www.opensource.org/licenses/mit-license.php)
 */

App::uses('Validation', 'Utility');

/**
 * Cake network socket connection class.
 *
 * Core base class for network communication.
 *
 * @package       Cake.Network
 */
class CakeSocket {

/**
 * Object description
 *
 * @var string
 */
	public $description = 'Remote DataSource Network Socket Interface';

/**
 * Base configuration settings for the socket connection
 *
 * @var array
 */
	protected $_baseConfig = array(
		'persistent'	=> false,
		'host'			=> 'localhost',
		'protocol'		=> 'tcp',
		'port'			=> 80,
		'timeout'		=> 30
	);

/**
 * Configuration settings for the socket connection
 *
 * @var array
 */
	public $config = array();

/**
 * Reference to socket connection resource
 *
 * @var resource
 */
	public $connection = null;

/**
 * This boolean contains the current state of the CakeSocket class
 *
 * @var boolean
 */
	public $connected = false;

/**
 * This variable contains an array with the last error number (num) and string (str)
 *
 * @var array
 */
	public $lastError = array();

/**
 * Constructor.
 *
 * @param array $config Socket configuration, which will be merged with the base configuration
 * @see CakeSocket::$_baseConfig
 */
	public function __construct($config = array()) {
		$this->config = array_merge($this->_baseConfig, $config);
		if (!is_numeric($this->config['protocol'])) {
			$this->config['protocol'] = getprotobyname($this->config['protocol']);
		}
	}

/**
 * Connect the socket to the given host and port.
 *
 * @return boolean Success
 * @throws SocketException
 */
	public function connect() {
		if ($this->connection != null) {
			$this->disconnect();
		}

		$scheme = null;
		if (isset($this->config['request']) && $this->config['request']['uri']['scheme'] == 'https') {
			$scheme = 'ssl://';
		}

		if ($this->config['persistent'] == true) {
			$this->connection = @pfsockopen($scheme . $this->config['host'], $this->config['port'], $errNum, $errStr, $this->config['timeout']);
		} else {
			$this->connection = @fsockopen($scheme . $this->config['host'], $this->config['port'], $errNum, $errStr, $this->config['timeout']);
		}

		if (!empty($errNum) || !empty($errStr)) {
			$this->setLastError($errNum, $errStr);
			throw new SocketException($errStr, $errNum);
		}

		$this->connected = is_resource($this->connection);
		if ($this->connected) {
			stream_set_timeout($this->connection, $this->config['timeout']);
		}
		return $this->connected;
	}

/**
 * Get the host name of the current connection.
 *
 * @return string Host name
 */
	public function host() {
		if (Validation::ip($this->config['host'])) {
			return gethostbyaddr($this->config['host']);
		}
		return gethostbyaddr($this->address());
	}

/**
 * Get the IP address of the current connection.
 *
 * @return string IP address
 */
	public function address() {
		if (Validation::ip($this->config['host'])) {
			return $this->config['host'];
		}
		return gethostbyname($this->config['host']);
	}

/**
 * Get all IP addresses associated with the current connection.
 *
 * @return array IP addresses
 */
	public function addresses() {
		if (Validation::ip($this->config['host'])) {
			return array($this->config['host']);
		}
		return gethostbynamel($this->config['host']);
	}

/**
 * Get the last error as a string.
 *
 * @return string Last error
 */
	public function lastError() {
		if (!empty($this->lastError)) {
			return $this->lastError['num'] . ': ' . $this->lastError['str'];
		}
		return null;
	}

/**
 * Set the last error.
 *
 * @param integer $errNum Error code
 * @param string $errStr Error string
 * @return void
 */
	public function setLastError($errNum, $errStr) {
		$this->lastError = array('num' => $errNum, 'str' => $errStr);
	}

/**
 * Write data to the socket.
 *
 * @param string $data The data to write to the socket
 * @return boolean Success
 */
	public function write($data) {
		if (!$this->connected) {
			if (!$this->connect()) {
				return false;
			}
		}
		$totalBytes = strlen($data);
		for ($written = 0, $rv = 0; $written < $totalBytes; $written += $rv) {
			$rv = fwrite($this->connection, substr($data, $written));
			if ($rv === false || $rv === 0) {
				return $written;
			}
		}
		return $written;
	}

/**
 * Read data from the socket. Returns false if no data is available or no connection could be
 * established.
 *
 * @param integer $length Optional buffer length to read; defaults to 1024
 * @return mixed Socket data
 */
	public function read($length = 1024) {
		if (!$this->connected) {
			if (!$this->connect()) {
				return false;
			}
		}

		if (!feof($this->connection)) {
			$buffer = fread($this->connection, $length);
			$info = stream_get_meta_data($this->connection);
			if ($info['timed_out']) {
				$this->setLastError(E_WARNING, __d('cake_dev', 'Connection timed out'));
				return false;
			}
			return $buffer;
		}
		return false;
	}

/**
 * Disconnect the socket from the current connection.
 *
 * @return boolean Success
 */
	public function disconnect() {
		if (!is_resource($this->connection)) {
			$this->connected = false;
			return true;
		}
		$this->connected = !fclose($this->connection);

		if (!$this->connected) {
			$this->connection = null;
		}
		return !$this->connected;
	}

/**
 * Destructor, used to disconnect from current connection.
 *
 */
	public function __destruct() {
		$this->disconnect();
	}

/**
 * Resets the state of this Socket instance to it's initial state (before Object::__construct got executed)
 *
 * @param array $state Array with key and values to reset
 * @return boolean True on success
 */
	public function reset($state = null) {
		if (empty($state)) {
			static $initalState = array();
			if (empty($initalState)) {
				$initalState = get_class_vars(__CLASS__);
			}
			$state = $initalState;
		}

		foreach ($state as $property => $value) {
			$this->{$property} = $value;
		}
		return true;
	}
}
