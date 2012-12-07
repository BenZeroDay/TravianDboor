<?php
/**
*
* @ This file is created by Decodeby.US
* @ deZender Public (PHP5 Decompiler)
*
* @	Version			:	1.0.0.0
* @	Author			:	Ps2Gamer & Cyko
* @	Release on		:	30.05.2011
* @	Official site	:	http://decodeby.us
*
*/

class FilterWordsModel extends ModelBase
{

    public function FilterWords( $text = "", $replace = "***" )
    {
        $patterns = array( "/([A-Z0-9._%+-]+)@([A-Z0-9.-]+)\\.([A-Z]{2,4})(\\((.+?)\\))?/i", "/\\b(?:(?:https?|ftp):\\/\\/|www\\.)[-a-z0-9+&@#\\/%?=~_|!:,.;]*[-a-z0-9+&@#\\/%=~_|]/i" );
        $badwor = $this->provider->fetchResultSet( "SELECT * FROM `g_words`" );
        while ( $badwor->next( ) )
        {
            $patterns[] = sprintf( "/(?<!\\pL)(%s)(?!\\pL)/u", $badwor->row['word'] );
        }
        return preg_replace( $patterns, $replace, $text );
    }

}

?>
