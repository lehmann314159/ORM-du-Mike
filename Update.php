<?
require_once( "classes/ORM/DB.php" );

// This class extends DB for
// Update.  Be careful.
class Update extends DB
{
	// set columns vs where columns
	protected $mSetColumnList = array();
	public function addSetColumn( $inCol )
		{ $this->mSetColumnList[] = $inCol; }
	public function clearSetColumn()
		{ $this->mSetColumnList = array(); }

	protected $mWhereColumnList = array();
	public function addWhereColumn( $inCol )
		{ $this->mWhereColumnList[] = $inCol; }
	public function clearWhereColumn()
		{ $this->mWhereColumnList = array(); }


    // Meta-/query-centric
    public function isValidQuery()
    {   
        if ( ! $this->hasTables() )  { return false; }
        if ( ! $this->hasColumns() ) { return false; }
        if ( ! $this->hasValues() )  { return false; }
        return true;
    }   

    public function showQuery( $verbose = false )
    {   
        $this->prepareQuery( $verbose );
        return $this->getQueryString();
    }   

    public function prepareQuery( $verbose = false )
    {   
        $rString  = "UPDATE" . $this->generateTables() . "SET";
        $rString .= $this->generateAssignments();
        $rString .= "WHERE" . $this->generateWheres();
        $this->setQueryString( $rString );
		if ( $verbose )
			{ print "query is: +$rString+\n"; }
        return true;
    }   

    public function generateAssignments()
    {   
        $assignments = array();
        for ( $i = 0; $i < count( $this->mColumnList ); $i++ )
        {   
			if ( in_array( $this->mColumnList[$i], $this->mSetColumnList ) )
			{
   	         	$assignments[] = $this->mColumnList[$i].' = '.(
    	            is_null( $this->mValueList[$i] )
        	        ? 'NULL'
            	    : $this->mValueList[$i]
        	    );  
			}
        }   

        return ' ' . implode( ', ', $assignments ) . ' ';
	}
}
?>
