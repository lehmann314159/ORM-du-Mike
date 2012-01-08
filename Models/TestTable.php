<?PHP
// Model Class for the test table
require_once( "classes/ORM/Model.php" );

class TestTable extends Model
{
	// Constructor
	function __construct()
	{
		$this->setTable( "TestTable" );
		$this->addFieldList( "id", "name", "quest", "favorite_color" );
	}
}
?>
