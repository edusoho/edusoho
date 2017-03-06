<?php

namespace AppBundle\Common;

class JsonToolkit
{
    public static function prettyPrint($json)
    {
        $result = '';
        $level = 0;
        $inQuotes = false;
        $inEscape = false;
        $endsLineLevel = null;
        $jsonLength = strlen($json);

        for ($i = 0; $i < $jsonLength; ++$i) {
            $char = $json[$i];
            $newLineLevel = null;
            $post = '';
            if ($endsLineLevel !== null) {
                $newLineLevel = $endsLineLevel;
                $endsLineLevel = null;
            }
            if ($inEscape) {
                $inEscape = false;
            } elseif ($char === '"') {
                $inQuotes = !$inQuotes;
            } elseif (!$inQuotes) {
                switch ($char) {
                    case '}': case ']':
                        $level--;
                        $endsLineLevel = null;
                        $newLineLevel = $level;
                        break;

                    case '{': case '[':
                        $level++;
                    case ',':
                        $endsLineLevel = $level;
                        break;

                    case ':':
                        $post = ' ';
                        break;

                    case ' ': case "\t": case "\n": case "\r":
                        $char = '';
                        $endsLineLevel = $newLineLevel;
                        $newLineLevel = null;
                        break;
                }
            } elseif ($char === '\\') {
                $inEscape = true;
            }
            if ($newLineLevel !== null) {
                $result .= "\n".str_repeat("\t", $newLineLevel);
            }
            $result .= $char.$post;
        }

        return $result;
    }
}
