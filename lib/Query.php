<?php

namespace Palasthotel\Grid\Drupal;

use Drupal\Core\Database\Database;
use mysqli;
use mysqli_result;
use Palasthotel\Grid\AbstractQuery;

/**
 * Class GridQuery
 * @package Palasthotel\Grid\WordPress
 */
class Query extends AbstractQuery {

  /**
	 * @return string
	 */
	public function prefix() {
		return Database::getConnection()->tablePrefix();
	}

	/**
	 * @var mysqli
	 */
	var $connection;

	/**
	 * @return mysqli
	 */
	private function getConnection(){

    $conn=Database::getConnection();
    $opts=$conn->getConnectionOptions();


		if($this->connection != null) return $this->connection;

		$connection = new mysqli( $opts['host'], $opts['username'], $opts['password'], $opts['database'] );
    $connection->set_charset("utf8");

		if ( $connection->connect_errno ) {
			error_log( "WP Grid: " . $connection->connect_error, 4 );
			die( "WP Grid could not connect to database." );
		}
		$this->connection = $connection;
		return $connection;
	}

	/**
	 * @param string $sql
	 *
	 * @return mysqli_result
	 */
	public function execute( $sql ) {
		return $this->getConnection()->query($sql);
	}

	/**
	 * @param string $str
	 *
	 * @return string
	 */
	public function real_escape_string( $str ) {
		return $this->getConnection()->real_escape_string($str);
	}

	/**
	 * on object destruction
	 */
	public function __destruct(){
		if($this->connection) $this->connection->close();
	}
}
