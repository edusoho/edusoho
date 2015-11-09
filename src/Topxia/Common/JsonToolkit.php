<?php
namespace Topxia\Common;

class JsonToolkit
{
	public static function prettyPrint( $json )
    {
        $result = '';
        $level = 0;
        $inQuotes = false;
        $inEscape = false;
        $endsLineLevel = NULL;
        $jsonLength = strlen( $json );

        for( $i = 0; $i < $jsonLength; $i++ ) {
            $char = $json[$i];
            $newLineLevel = NULL;
            $post = "";
            if( $endsLineLevel !== NULL ) {
                $newLineLevel = $endsLineLevel;
                $endsLineLevel = NULL;
            }
            if ( $inEscape ) {
                $inEscape = false;
            } else if( $char === '"' ) {
                $inQuotes = !$inQuotes;
            } else if( ! $inQuotes ) {
                switch( $char ) {
                    case '}': case ']':
                        $level--;
                        $endsLineLevel = NULL;
                        $newLineLevel = $level;
                        break;

                    case '{': case '[':
                        $level++;
                    case ',':
                        $endsLineLevel = $level;
                        break;

                    case ':':
                        $post = " ";
                        break;

                    case " ": case "\t": case "\n": case "\r":
                        $char = "";
                        $endsLineLevel = $newLineLevel;
                        $newLineLevel = NULL;
                        break;
                }
            } else if ( $char === '\\' ) {
                $inEscape = true;
            }
            if( $newLineLevel !== NULL ) {
                $result .= "\n".str_repeat( "\t", $newLineLevel );
            }
            $result .= $char.$post;
        }

        return $result;
    }
}
