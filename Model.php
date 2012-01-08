<?PHP
// Base class for Models

require_once( "classes/ORM/Delete.php" );
require_once( "classes/ORM/Insert.php" );
require_once( "classes/ORM/Select.php" );
require_once( "classes/ORM/Update.php" );

class Model
{
	// properties
	protected $_Table;
	protected $_FieldList = array();
	protected $_NumAffectedFields;
	protected $_DBQuery;
	const VX = "a6d52k826shfre19rklsytef4jkskbrsyqre";


	// Database operations
	public function delete() { return true; }
	public function select() { return true; }
	public function select2()
	{
		$select = new Select();
		$select->addColumn( "*" );
		$select->setTable( $this->getTable() );
		foreach ( $this->getFieldList() as $key )
		{
			$value = $this->getField( $key );
			$select->addWhereClause( "$key = $value" );
		}
		$select->executeQuery();
		$this->setDBQuery( $select );
		return $select->getNumAffectedRows();
	}

	public function fetchRow()
	{
		if ( ! $obj = $this->getDBQuery()->getNextRow() )
		{
			return false;
		}

		foreach ( get_object_vars( $obj ) as $key => $value )
		{
			$this->setField( "$key", $value );
		}

		return true;
	}

	public function insert()
	{
		$insert = new Insert();
		$insert->setTable( $this->getTable() );
		foreach ( $this->getFieldList() as $key )
			{ $insert->addPair( $key, $this->getField( $key ) ); }

		$insert->executeQuery();
		$this->setNumAffectedRows( $insert->getNumAffectedRows() );
		return $this->getNumAffectedRows();
	}

	public function update()
	{
		$update = new Update();
		$update->setTable( $this->getTable() );
		foreach ( $this->getFieldList() as $key )
			{ $update->addPair( $key, $this->getField( $key ) ); }

		$update->executeQuery( true );
		$this->setNumAffectedRows( $update->getNumAffectedRows() );
		return $this->getNumAffectedRows();
	}


	// Affected Rows
	public function setNumAffectedRows( $inNum ) { $this->_NumAffectedRows = $inNum; }
	public function getNumAffectedRows() { return $this->_NumAffectedRows; }


	// Table Name methods
	public function setTable( $inTable ) { $this->_Table = $inTable; }
	public function getTable() { return $this->_Table; }


	// Add one field
	public function addField( $inField )
		{ $this->_FieldList[$inField] = self::VX; }

	// Add many fields
	public function addFieldList( $inArray )
	{
		if ( is_array( $inArray ) )
		{
			foreach ( $inArray as $element )
				{ $this->_FieldList[$element] = self::VX; }
		}
		else $this->addFieldList( func_get_args() );
	}

	// get all filled fields
	public function getFieldList()
	{
		$returnList = array();
		foreach ( $this->_FieldList as $key => $value )
			{ if ( $value != self::VX ) { array_push( $returnList, $key ); } }

		return $returnList;
	}

	// Get at our underlying query monster
	public function setDBQuery( $inDBQuery ) { $this->_DBQuery = $inDBQuery; }
	public function getDBQuery() { return $this->_DBQuery; }


	// general mutators and accessors
	public function getField( $inField )
	{
		if ( $this->_FieldList[$inField] == self::VX )
			{ return NULL; }
		else
			{ return $this->_FieldList[$inField]; }
	}

	public function setField( $inField, $inValue )
	{
		if ( ! $inValue )
			{ $this->_FieldList[$inField] = self::VX; }
		else
			{ $this->_FieldList[$inField] = $inValue; }
	}
}
?>
