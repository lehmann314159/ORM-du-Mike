<?PHP

require_once( "classes/ORM/DB.php" );

class Select extends DB
{
    // protected members
    protected $mSortColumnList = array();
    protected $mSortDirectionList = array();
    protected $mWhereClauseList = array();
    protected $mGroupByList = array();
    protected $mHavingList = array();
    protected $mLimitOffset = 0;
    protected $mLimitSize = 0;

    // Meta- or query-centric methods
    public function isValidQuery()
    {   
        if ( ! $this->hasColumns() ) { return false; }
        if ( ! $this->hasTables() ) { return false; }
        return true;
    }   

    public function prepareQuery( $verbose = false )
    {   
        if ( ! $this->isValidQuery() )
            { return false; }

        // Our verb of choice
        $rString = "SELECT ";

        // Hints
        $rString .= $this->generateHints();
        $rString .= ( $this->getDistinct() ) ? " DISTINCT " : " ALL ";

        // columns to retrieve, along with aliases
        $rString .= $this->generateColumns();
        $verbose && $rString .= "\n";

        // the table(s) to draw from, along with joins
        $rString .= " FROM " . $this->generateTables();
        $verbose && $rString .= "\n";

        // Where clause
        if ( count( $this->mWhereClauseList ) ) 
            { $rString .= " WHERE " . $this->generateWheres(); }
        $verbose && $rString .= "\n";

        // Grouping
        if ( count( $this->mGroupByList ) )
            { $rString .= " GROUP BY " . $this->generateGroupBy(); }
        $verbose && $rString .= "\n";

        // Having
        if ( count( $this->mHavingList ) )
            { $rString .= " HAVING " . $this->generateHaving(); }
        $verbose && $rString .= "\n";

        // Ordering
        if ( count( $this->mSortColumnList ) )
            { $rString .= " ORDER BY " . $this->generateSortColumns(); }
        $verbose && $rString .= "\n";

        // limit clause
        if ( $this->getLimitSize() )
            { $rString .= $this->generateLimitClause(); }
        $verbose && $rString .= "\n";
        // assign to protected member
        $this->setQueryString( $rString );

        return true;
    }


    // Distinct versus all
    public function getDistinct() { return $this->mDistinct; }

    public function setDistinct( $inFlag = true ) { $this->mDistinct = $inFlag; }


    // Limit functions
    public function setLimitSize( $inLimitSize ) { $this->mLimitSize = $inLimitSize; }

    public function getLimitSize() { return $this->mLimitSize; }

    public function setLimitOffset( $inLimitOffset ) { $this->mLimitOffset = $inLimitOffset; }

    public function getLimitOffset() { return $this->mLimitOffset; }

    public function generateLimitClause()
    {
        $rString;
        if ( $this->getLimitSize() )
        {
            if ( $this->getLimitOffset() )
                { $rString .= " LIMIT $this->getLimitOffset(), $this->getLimitSize()"; }
            else
                { $rString .= " LIMIT $this->getLimitSize()"; }
        }
    }


    // Order functions
    public function clearSortColumnList() { $this->mSortColumnList = array(); }

    public function addSortColumn( $inColumnName, $inDirection = DB::SORT_ASCENDING )
    {
        array_push( $this->mSortColumnList, $inColumnName );
        array_push( $this->mSortDirectionList, $inDirection );
        return true;
    }

    public function generateSortColumns()
    {
        $rString = " " . $this->mSortColumnList[0] . " " .$this->mSortDirectionList[0];
        for ( $i = 1; $i < count( $this->mSortColumnList ); $i++ )
        {
            $rString .= ", ";
            $rString .= $this->mSortColumnList[$i] . " ";
            $rString .= $this->mSortDirectionList[$i];
        }
        return $rString . " ";
    }


    // Group / Having functions
    public function clearGroupByList() { $this->mGroupByList = array(); }

    public function addGroupBy( $inColumn )
    {
        array_push( $this->mGroupByList, $inColumn );
    }

    public function generateGroupBy()
    {
        $rString = " " . $this->mGroupByList[0];
        for ( $i = 1; $i < count( $this->mGroupByList ); $i++ )
        {
            $rString .= ", ";
            $rString .= $this->mGroupByList[$i] . " ";
        }
        return $rString . " ";
    }

    public function clearHavingList() { $this->mHavingList = array(); }

    public function addHaving( $inColumn )
    {
        array_push( $this->mHavingList, $inColumn );
    }

    public function generateHaving()
    {
        $rString = " " . $this->mHavingList[0];
        for ( $i = 1; $i < count( $this->mHavingList ); $i++ )
        {
            $rString .= ", ";
            $rString .= $this->mHavingList[$i] . " ";
        }
        return $rString . " ";
    }

}
?>
