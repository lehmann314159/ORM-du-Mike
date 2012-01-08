<?
require_once( "classes/ORM/DB.php" );

// This class extends DB for
// Insertions.  All hail the overlord.

class Insert extends DB
{
    // protected member list
    protected $mSubQuery = NULL;
    protected $mInsertId = NULL;


    // Meta-/query-centric
    public function isValidQuery()
    {   
        if ( ! $this->hasTable() ) { return false; }
        if ( ! $this->hasColumns() ) { return false; }
        if ( $this->hasValues() || $this->hasSubselect() ) { return true; }
        return false;
    }   

    public function showQuery( $verbose = false )
    {   
        $this->prepareQuery( $verbose );
        return $this->getQueryString();
    }   


    // show the query
    public function prepareQuery( $verbose = false )
    {   
        if ( ! $this->isValidQuery() ) { return false; }

        $rString = "INSERT INTO " . $this->generateTables();
        $rString .= $this->generateColumns();
        $verbose and $rString .= "\n";

        if ( $this->hasSubselect() )
            { $rString .= " FROM " . $this->getSubselect()->showQuery(); }
        else
            { $rString .= " VALUES " . $this->generateValues(); }
        $verbose and $rString .= "\n";
        $this->setQueryString( $rString );
        return true;
    }   


    // create sql clause for insert-centric column display
    public function generateColumns()
    {
        $rString = " (" . $this->mColumnList[0];
        if ( ! is_null( $this->mAliasList[0] ) )
            { $rString .= " AS " . $this->mAliasList[0]; }
        for ( $i = 1; $i < count( $this->mColumnList ); $i++ )
        {
            $col = $this->mColumnList[$i];
            $alias = $this->mAliasList[$i];
            $rString .= ", $col";
            if ( ! is_null( $alias ) ) { $rString .= " AS $alias"; }
        }

        $rString .= ") ";
        return $rString;
    }


    // create sql clause for insert-centric value display
    public function generateValues()
    {
        $rString = " (" . implode( ',', $this->mValueList ) . ") ";
        return $rString;
    }

    // Support for INSERT... FROM SELECT
    public function getSubselect() { return $this->mSubQuery; }

    public function hasSubselect()
        { return ( is_a( $this->mSubQuery, "Select" ) ); }

    public function setSubselect( $inSubselect )
    {
        if ( is_a( $inSubselect, "Select" ) )
            { $mSubQuery = $inSubselect->showQuery(); }
        else
            { $mSubQuery = $inSubselect; }
    }


    public function getInsertId(){
        return( $this->mInsertId );
    }

    public function executeQuery(){
        $return = parent::executeQuery();
        $this->mInsertId = ( $return ? mysql_insert_id( ML::getConn() ) : null );
        return( $return );
    }
}
?>
