<?PHP
// Connection.php
// -- The super secret connection file that hides from strangers

require_once( "classes/ORM/Secrets.php" );
class Connection
{
	private static $_Conn;

	// Return the connection object, creating it if need be
	public static function getConn()
	{
		// If we have an open connection, just use it.
		// Otherwise, try to make one, and then use it.
		if ( ! self::$_Conn )
		{
			self::$_Conn = mysql_connect(
				Secrets::getHost(),
				Secrets::getUser(),
				Secrets::getPassword()
			);
			@mysql_selectdb( Secrets::getSchema() );
		}

		// No connection at this point means something is bad.
		if ( ! self::$_Conn )
		{
			$errString = "Could not connect to " . Secrets::getHost()
				. " using " . Secrets::getUser() . ":"
				. Secrets::getPassword();
			throw new Exception( $errString );
		}

		return self::$_Conn;
	}
}
 ?>
