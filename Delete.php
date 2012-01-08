<?PHP
require_once( "classes/ORM/DB.php" );

class Delete extends DB
{
    protected $mLimitSize = -1; 

    public function isValidQuery(){
        if ( count( $this->mTableList ) != 1 ){ return false; }
        if ( count( $this->mWhereClauseList ) == 0 ){ return false; }
        return true;
    }   

    public function prepareQuery( $verbose = false ){
        if ( ! $this->isValidQuery() )
            { return false; }

        $rString = 'DELETE FROM '
            . $this->generateTables()
            . ( $verbose ? "\n" : ' ' )
            . 'WHERE ' . $this->generateWheres()
            . ( 
                (int)$this->getLimitSize() >= 0
                ? ( 
                    $verbose ? "\n" : ' ' 
                ) . 'LIMIT ' . (int)$this->getLimitSize()
                : ''
            );  
        $this->setQueryString( $rString );
        return true;
    }   

    public function showQuery(){
        $this->prepareQuery();
        return( $this->getQueryString() );
    }   

    // Limit functions
    public function setLimitSize( $inLimitSize ) { $this->mLimitSize = (int)$inLimitSize; }

    public function getLimitSize() { return $this->mLimitSize; }
}
?>
