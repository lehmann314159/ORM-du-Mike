<?PHP

require_once( "classes/ORM/Models/TestTable.php" )

$myTestTable = new TestTable();
$myTestTable->select2();
while ( $obj = $myTestTable->fetchRow() )
{
	$name  = $obj->getField( "name" );
	$quest = $obj->getField( "quest" );
	$color = $obj->getField( "color" );

	print "$name (wearing a $color snuggie) is on this quest: $quest.<br>\n";
}
?>
