<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

class MysqlModel
{

    public $provider = NULL;

    public function MysqlModel( )
    {
        $this->provider = new MysqlProvider( );
    }

    public function dispose( )
    {
        $this->provider->close( );
    }

}

class MysqlResultSet
{

    public $_result = NULL;
    public $row = NULL;

    public function MysqlResultSet( $result )
    {
        $this->_result = $result;
    }

    public function next( )
    {
        $this->row = mysql_fetch_array( $this->_result, MYSQL_ASSOC );
        $returnValue = $this->row != NULL;
        if ( !$returnValue )
        {
            $this->free( );
        }
        return $returnValue;
    }

    public function free( )
    {
        mysql_free_result( $this->_result );
        unset( $row ); //bug whats this i dont know lol was this  unset( $FN_5695008['row'] );
    }

}

class MysqlProvider
{

    public $debug = FALSE;
    public $properties = NULL;
    public $_conn = NULL;

    public function open( )
    {
        static $c = NULL;
        if ( $c == NULL )
        {
            $c = array( );
        }
        $connKey = md5( $this->properties['host'].$this->properties['user'].$this->properties['password'].$this->properties['database'] );
        if ( !isset( $c[$connKey] ) )
        {
            $c[$connKey] = NULL;
        }
        $this->_conn =& $c[$connKey];
        if ( $this->_conn != NULL )
        {
            return TRUE;
        }
        $c[$connKey] = $this->_conn = mysql_connect( $this->properties['host'], $this->properties['user'], $this->properties['password'] );
        if ( $this->_conn == NULL )
        {
            return FALSE;
        }
        if ( mysql_select_db( $this->properties['database'] ) == NULL )
        {
            return FALSE;
        }
        return TRUE;
    }

    public function close( )
    {
        if ( $this->_conn == NULL )
        {
            return;
        }
        mysql_close( $this->_conn );
        $this->_conn = NULL;
    }

    public function executeQuery( $sqlStatement, $sqlParams = NULL )
    {
        $this->_executeQuery( $sqlStatement, $sqlParams, 1 );
    }

    public function executeQuery2( $sqlStatement, $sqlParams = NULL )
    {
        return $this->_executeQuery( $sqlStatement, $sqlParams, 2 );
    }

    public function fetchScalar( $sqlStatement, $sqlParams = NULL )
    {
        return $this->_executeQuery( $sqlStatement, $sqlParams, 3 );
    }

    public function fetchResultSet( $sqlStatement, $sqlParams = NULL )
    {
        return $this->_executeQuery( $sqlStatement, $sqlParams, 4 );
    }

    public function fetchRow( $sqlStatement, $sqlParams = NULL )
    {
        $result = $this->fetchResultSet( $sqlStatement, $sqlParams );
        if ( !$result->next( ) )
        {
            return;
        }
        $data = array( );
        foreach ( $result->row as $k => $v )
        {
            $data[$k] = $v;
        }
        $result->free( );
        return $data;
    }

    public function executeBatchQuery( $batchStatement, $separator = ";" )
    {
        $queryArray = explode( $separator, $batchStatement );
        $i = 0;
        $count = sizeof( $queryArray );
        while ( $i < $count )
        {
            $query = trim( $queryArray[$i] );
            if ( $query != "" )
            {
                $this->executeQuery( $query );
            }
            ++$i;
        }
    }

    public function _executeQuery( $sqlStatement, $sqlParams, $executionType )
    {
        $this->open( );
        if ( $sqlParams != NULL && is_array( $sqlParams ) )
        {
            $safe_params = array( );
            foreach ( $sqlParams as $paramValue )
            {
                $safe_params[] = mysql_real_escape_string( $paramValue, $this->_conn );
            }
            $sqlStatement = vsprintf( $sqlStatement, $safe_params );
        }
        if ( $this->debug )
        {
            echo $sqlStatement;
            echo "<hr/>";
        }
        $result = mysql_query( $sqlStatement, $this->_conn );
        switch ( $executionType )
        {
        case 1 :
            return;
        case 2 :
            return mysql_affected_rows( $this->_conn );
        case 3 :
            $row = mysql_fetch_row( $result );
            $returnValue = $row[0];
            mysql_free_result( $result );
            return $returnValue;
        case 4 :
            return new MysqlResultSet( $result );
        }
    }

}

?>
