<?PHP

require_once( "classes/ORM/Connection.php" );

// Base class for database functionality
class DB
{
    // Constants
    const INNER_JOIN      = 'INNER JOIN';
    const RIGHT_JOIN      = 'RIGHT OUTER JOIN';
    const LEFT_JOIN       = 'LEFT OUTER JOIN';
    const FULL_JOIN       = 'FULL JOIN';
    const SORT_ASCENDING  = 'ASC';
    const SORT_DESCENDING = 'DESC';


    // protected members
    protected $mQueryString;
    protected $mColumnList = array();
    protected $mAliasList = array();
    protected $mValueList = array();
    protected $mTableList = array();
    protected $mJoinConditionList = array();
    protected $mJoinStyleList = array();
    protected $mWhereClauseList = array();
    protected $mHintList = array();
    protected $mConnection;
    protected $mNumAffectedRows;
    protected $mResultSet;


    // Constructor
    function DB() { $this->setConnection( Connection::getConn() ); }


    // Query-centric functions
    public function isValidQuery() { return false; }

    public function showQuery( $verbose = false )
    {
        $this->prepareQuery( $verbose );
        return $this->getQueryString();
    }

    public function prepareQuery( $verbose = false ) { return true; }

    public function executeQuery( $verbose = false )
    {
        if ( ! $this->isValidQuery() )
            { $this->myThrow( "Mal-formed query.  Not performed." ); }

        if ( ! $this->prepareQuery( $verbose ) )
            { $this->myThrow( "Cannot prepare query" ); }

        $result = mysql_query( $this->getQueryString(), Connection::getConn() );
        if  ( ! $result )
        {
            $errorString  = "Cannot execute query: error processing ";
            $errorString .= $this->getQueryString() . ", MySQL Error: '";
            $errorString .=  mysql_error();
            $this->myThrow( $errorString );
        }
        $this->setNumAffectedRows( @mysql_affected_rows( Connection::getConn() ) );
        $this->setResultSet( $result );
        return true;
    }

    // Hints -- These appear before the column list
    public function clearHintList() { $this->mHintList = array(); }

    public function addHint( $inHint ) { array_push( $this->mHintList, $inHint ); }

    public function generateHints()
    {
        $rString = " " . $this->mHintList[0];
        for ( $i = 1; $i < count( $this->mHintList ); $i++ )
            { $rString .= " " . $this->mHintList[$i]; }
        return $rString . " ";
    }


    // Columns -- These are for selection, not for ordering
    // Allows inclusion of a single column, with an optional alias
    public function addColumn( $inColumnName, $inAlias = NULL )
    {
        array_push( $this->mColumnList, $inColumnName );
        array_push( $this->mAliasList, $inAlias );
        return true;
    }

    // Allows inclusion of multiple columns, without aliases
    public function addColumnList()
    {
        foreach ( func_get_args() as $element )
        {
            if ( is_array( $element ) )
                { $this->mColumnList += $element; }
            else
            {
                array_push( $this->mColumnList, $element );
                array_push( $this->mAliasList, NULL );
            }
        }
        return true;
    }
    public function hasColumns()
        { return ( count( $this->mColumnList ) > 0 ); }

    public function generateColumns()
    {
        $rString = " " . $this->mColumnList[0];
        if ( ! is_null( $this->mAliasList[0] ) )
            { $rString .= " AS " . $this->mAliasList[0]; }
        for ( $i = 1; $i < count( $this->mColumnList ); $i++ )
        {
            $col = $this->mColumnList[$i];
            $alias = $this->mAliasList[$i];
            $rString .= ", $col";
            if ( ! is_null( $alias ) ) { $rString .= " AS $alias"; }
        }
        return $rString . " ";
    }

    public function hasValues()
        { return ( count( $this->mColumnList ) > 0 ); }

    public function generateValues()
    {
        $rString = " " . $this->mValueList[0];
        for ( $i = 1; $i < count( $this->mValueList ); $i++ )
        {
            $val = $this->mValueList[$i];
            $rString .= ", $val";
        }
        return $rString . " ";
    }


    // Table/Join functions
    // Use this function if you have a single table
    // no good for joins
    public function setTable( $inTable )
    {
        $this->mTableList = array();
        array_push( $this->mTableList, $inTable );
        return true;
    }
                                   // Use these methods if you use multiple tables
    public function startJoin( $inTable )
    {
        $this->mJoinConditionList = array();
        $this->mJoinStyleList = array();
        $this->mTableList = array();
        array_push( $this->mTableList, $inTable );
    }

    public function addJoin( $inTable2, $inExpression, $inStyle = DB::INNER_JOIN )
    {
        // Add $inTable2, $inExpression, $inStyle
        array_push( $this->mTableList, $inTable2 );
        array_push( $this->mJoinConditionList, $inExpression );
        array_push( $this->mJoinStyleList, $inStyle );
        return true;
    }

    public function hasTable() { return $this->hasTables(); }

    public function hasTables()
        { return ( count( $this->mTableList ) > 0 ); }

    public function generateTables()
    {
        $cheat = array_shift( $this->mTableList );
        $rString = " " . $cheat;
        for ( $i = 0; $i < count( $this->mTableList ); $i++ )
        {
            $rString .= " " . $this->mJoinStyleList[$i] . " ";
            $rString .= $this->mTableList[$i];
            $rString .= " ON " . $this->mJoinConditionList[$i];
        }
        array_unshift( $this->mTableList, $cheat );
        return $rString . " ";
    }


    // Where clause
    // Where clauses can be complicated, and so is how we're handling them
    // $mWhereClauseList is an array of where fragments.  When assembled
    // those fragments are anded together.
                                               
    // $mWhereClauseList is an array of where fragments.  When assembled
    // those fragments are anded together.
    //
    // To make use of OR statements utilize makeWhereClause().  It takes
    // any number of arguments, the last of which is the operator that is
    // to be used to connect the arguments.  After the arguments are
    // connected, the whole thing is wrapped up in parens and returned as
    // a string.
    // Using these 2 methods you should be able to construct where clauses
    // of arbitrary complexity.  It's a little clunky, but lets you avoid
    // glomming everything onto the end in a nightmare of binary adds.
    public function makeWhereClause()
    {
        $clauseList = MyArray::flatten( func_get_args() );
        $operator = array_pop( $clauseList );
        $firstClause = array_shift( $clauseList );
        $rString = "( $firstClause ";

        while ( count( $clauseList ) )
        {
            $tempClause = array_pop( $clauseList );
            $rString .= "$operator $tempClause ";
        }

        $rString .= ")";
        return $rString;
    }

    public function addWhereClause( $inClause )
    {
        array_push( $this->mWhereClauseList, $inClause );
    }

    public function generateWheres()
    {
        $rString = " " . $this->mWhereClauseList[0];
        for ( $i = 1; $i < count( $this->mWhereClauseList ); $i++ )
        {
            $rString .= " AND " . $this->mWhereClauseList[$i];
        }

        return $rString  . " ";
    }
    // Column/Value functions
    // These are used by insert and update, so I'm putting them
    // in the common area.
    public function addPair( $inColumnName, $inValue, $inQuoteValue = true )
    {
        array_push( $this->mColumnList, $inColumnName );
        $newValue = ( $inQuoteValue ? "'" . mysql_real_escape_string( $inValue ) . "'" : $inValue );
        array_push( $this->mValueList, $newValue );
        return true;
    }


    public function addPairs( array $pairs, $inQuoteValue = true ){
        foreach( $pairs as $name => $value ){
            $this->addPair( $name, $value, $inQuoteValue );
        }
        return true;
    }

    function myThrow( $errMsg )
    {
        throw( new Exception( $errMsg ) );
    }


    // Getters
    public function getQueryString() { return $this->mQueryString; }

    public function getConnection() { return $this->mConnection; }

    public function getNumAffectedRows() { return $this->mNumAffectedRows; }

    public function getResultSet() { return $this->mResultSet; }
    public function getNextRow() {
        if ( is_resource( $resource = $this->getResultSet() ) )
		{
			// we need this check, because an object
			// created from an empty array is non-null
            $chet = mysql_fetch_object( $resource );
			if ( ! $chet ) { return NULL; }
			$atkins = array();
			foreach ( get_object_vars( $chet ) as $property => $value )
			{
				$atkins[$property] = stripSlashes( $value );
			}
			return (object) $atkins;
        }
        return null;
    }

    // Setters
    public function setQueryString( $inQS ) { $this->mQueryString = $inQS; }

    public function setConnection( $inConn ) { $this->mConnection = $inConn; }

    public function setNumAffectedRows( $inNum ) { $this->mNumAffectedRows = $inNum; }

    public function setResultSet( $inRS) { $this->mResultSet = $inRS; }

}
?>
