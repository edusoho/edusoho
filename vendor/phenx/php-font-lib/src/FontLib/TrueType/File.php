<?php
/**
 * @package php-font-lib
 * @link    https://github.com/dompdf/php-font-lib
 * @author  Fabien Ménager <fabien.menager@gmail.com>
 * @license http://www.gnu.org/copyleft/lesser.html GNU Lesser General Public License
 */

namespace FontLib\TrueType;

use FontLib\AdobeFontMetrics;
use FontLib\Font;
use FontLib\BinaryStream;
use FontLib\Table\Table;
use FontLib\Table\DirectoryEntry;
use FontLib\Table\Type\glyf;
use FontLib\Table\Type\name;
use FontLib\Table\Type\nameRecord;

/**
 * TrueType font file.
 *
 * @package php-font-lib
 */
class File extends BinaryStream {
  /**
   * @var Header
   */
  public $header = array();

  private $tableOffset = 0; // Used for TTC

  private static $raw = false;

  protected $directory = array();
  protected $data = array();

  protected $glyph_subset = array();

  public $glyph_all = array();

  static $macCharNames = array(
    ".notdef", ".null", "CR",
    "space", "exclam", "quotedbl", "numbersign",
    "dollar", "percent", "ampersand", "quotesingle",
    "parenleft", "parenright", "asterisk", "plus",
    "comma", "hyphen", "period", "slash",
    "zero", "one", "two", "three",
    "four", "five", "six", "seven",
    "eight", "nine", "colon", "semicolon",
    "less", "equal", "greater", "question",
    "at", "A", "B", "C", "D", "E", "F", "G",
    "H", "I", "J", "K", "L", "M", "N", "O",
    "P", "Q", "R", "S", "T", "U", "V", "W",
    "X", "Y", "Z", "bracketleft",
    "backslash", "bracketright", "asciicircum", "underscore",
    "grave", "a", "b", "c", "d", "e", "f", "g",
    "h", "i", "j", "k", "l", "m", "n", "o",
    "p", "q", "r", "s", "t", "u", "v", "w",
    "x", "y", "z", "braceleft",
    "bar", "braceright", "asciitilde", "Adieresis",
    "Aring", "Ccedilla", "Eacute", "Ntilde",
    "Odieresis", "Udieresis", "aacute", "agrave",
    "acircumflex", "adieresis", "atilde", "aring",
    "ccedilla", "eacute", "egrave", "ecircumflex",
    "edieresis", "iacute", "igrave", "icircumflex",
    "idieresis", "ntilde", "oacute", "ograve",
    "ocircumflex", "odieresis", "otilde", "uacute",
    "ugrave", "ucircumflex", "udieresis", "dagger",
    "degree", "cent", "sterling", "section",
    "bullet", "paragraph", "germandbls", "registered",
    "copyright", "trademark", "acute", "dieresis",
    "notequal", "AE", "Oslash", "infinity",
    "plusminus", "lessequal", "greaterequal", "yen",
    "mu", "partialdiff", "summation", "product",
    "pi", "integral", "ordfeminine", "ordmasculine",
    "Omega", "ae", "oslash", "questiondown",
    "exclamdown", "logicalnot", "radical", "florin",
    "approxequal", "increment", "guillemotleft", "guillemotright",
    "ellipsis", "nbspace", "Agrave", "Atilde",
    "Otilde", "OE", "oe", "endash",
    "emdash", "quotedblleft", "quotedblright", "quoteleft",
    "quoteright", "divide", "lozenge", "ydieresis",
    "Ydieresis", "fraction", "currency", "guilsinglleft",
    "guilsinglright", "fi", "fl", "daggerdbl",
    "periodcentered", "quotesinglbase", "quotedblbase", "perthousand",
    "Acircumflex", "Ecircumflex", "Aacute", "Edieresis",
    "Egrave", "Iacute", "Icircumflex", "Idieresis",
    "Igrave", "Oacute", "Ocircumflex", "applelogo",
    "Ograve", "Uacute", "Ucircumflex", "Ugrave",
    "dotlessi", "circumflex", "tilde", "macron",
    "breve", "dotaccent", "ring", "cedilla",
    "hungarumlaut", "ogonek", "caron", "Lslash",
    "lslash", "Scaron", "scaron", "Zcaron",
    "zcaron", "brokenbar", "Eth", "eth",
    "Yacute", "yacute", "Thorn", "thorn",
    "minus", "multiply", "onesuperior", "twosuperior",
    "threesuperior", "onehalf", "onequarter", "threequarters",
    "franc", "Gbreve", "gbreve", "Idot",
    "Scedilla", "scedilla", "Cacute", "cacute",
    "Ccaron", "ccaron", "dmacron"
  );

  private function uniord (string $c, string $encoding = null) {
    if (function_exists("mb_ord")) {
      if (PHP_VERSION_ID < 80000 && $encoding === null) {
          // in PHP < 8 the encoding argument, if supplied, must be a valid encoding
          $encoding = "UTF-8";
      }
      return mb_ord($c, $encoding);
    }

    if ($encoding != "UTF-8" && $encoding !== null) {
      $c = mb_convert_encoding($c, "UTF-8", $encoding);
    }

    $length = mb_strlen(mb_substr($c, 0, 1), '8bit');
    $ord = false;
    $bytes = [];
    $numbytes = 1;
    for ($i = 0; $i < $length; $i++) {
      $o = \ord($c[$i]); // get one string character at time
      if (\count($bytes) === 0) { // get starting octect
        if ($o <= 0x7F) {
          $ord = $o;
          $numbytes = 1;
        } elseif (($o >> 0x05) === 0x06) { // 2 bytes character (0x06 = 110 BIN)
          $bytes[] = ($o - 0xC0) << 0x06;
          $numbytes = 2;
        } elseif (($o >> 0x04) === 0x0E) { // 3 bytes character (0x0E = 1110 BIN)
          $bytes[] = ($o - 0xE0) << 0x0C;
          $numbytes = 3;
        } elseif (($o >> 0x03) === 0x1E) { // 4 bytes character (0x1E = 11110 BIN)
          $bytes[] = ($o - 0xF0) << 0x12;
          $numbytes = 4;
        } else {
          $ord = false;
          break;
        }
      } elseif (($o >> 0x06) === 0x02) { // bytes 2, 3 and 4 must start with 0x02 = 10 BIN
          $bytes[] = $o - 0x80;
          if (\count($bytes) === $numbytes) {
            // compose UTF-8 bytes to a single unicode value
            $o = $bytes[0];
            for ($j = 1; $j < $numbytes; $j++) {
              $o += ($bytes[$j] << (($numbytes - $j - 1) * 0x06));
            }
            if ((($o >= 0xD800) and ($o <= 0xDFFF)) or ($o >= 0x10FFFF)) {
              // The definition of UTF-8 prohibits encoding character numbers between
              // U+D800 and U+DFFF, which are reserved for use with the UTF-16
              // encoding form (as surrogate pairs) and do not directly represent
              // characters.
              return false;
            } else {
              $ord = $o; // add char to array
            }
            // reset data for next char
            $bytes = [];
            $numbytes = 1;
          }
      } else {
        $ord = false;
        break;
      }
    }

    return $ord;
  }

  function getTable() {
    $this->parseTableEntries();

    return $this->directory;
  }

  function setTableOffset($offset) {
    $this->tableOffset = $offset;
  }

  function parse() {
    $this->parseTableEntries();

    $this->data = array();

    foreach ($this->directory as $tag => $table) {
      if (empty($this->data[$tag])) {
        $this->readTable($tag);
      }
    }
  }

  function utf8toUnicode($str) {
    $len = mb_strlen($str, '8bit');
    $out = array();

    for ($i = 0; $i < $len; $i++) {
      $uni = -1;
      $h   = ord($str[$i]);

      if ($h <= 0x7F) {
        $uni = $h;
      }
      elseif ($h >= 0xC2) {
        if (($h <= 0xDF) && ($i < $len - 1)) {
          $uni = ($h & 0x1F) << 6 | (ord($str[++$i]) & 0x3F);
        }
        elseif (($h <= 0xEF) && ($i < $len - 2)) {
          $uni = ($h & 0x0F) << 12 | (ord($str[++$i]) & 0x3F) << 6 | (ord($str[++$i]) & 0x3F);
        }
        elseif (($h <= 0xF4) && ($i < $len - 3)) {
          $uni = ($h & 0x0F) << 18 | (ord($str[++$i]) & 0x3F) << 12 | (ord($str[++$i]) & 0x3F) << 6 | (ord($str[++$i]) & 0x3F);
        }
      }

      if ($uni >= 0) {
        $out[] = $uni;
      }
    }

    return $out;
  }

  function getUnicodeCharMap() {
    $subtable = null;
    foreach ($this->getData("cmap", "subtables") as $_subtable) {
      if ($_subtable["platformID"] == 0 || ($_subtable["platformID"] == 3 && $_subtable["platformSpecificID"] == 1)) {
        $subtable = $_subtable;
        break;
      }
    }

    if ($subtable) {
      return $subtable["glyphIndexArray"];
    }

    $system_encodings = mb_list_encodings();
    $system_encodings = array_change_key_case(array_fill_keys($system_encodings, true), CASE_UPPER);
    foreach ($this->getData("cmap", "subtables") as $_subtable) {
      $encoding = null;
      switch ($_subtable["platformID"]) {
        case 3:
          switch ($_subtable["platformSpecificID"]) {
            case 2:
              if (\array_key_exists("SJIS", $system_encodings)) {
                $encoding = "SJIS";
              }
              break;
            case 3:
              if (\array_key_exists("GB18030", $system_encodings)) {
                $encoding = "GB18030";
              }
              break;
            case 4:
              if (\array_key_exists("BIG-5", $system_encodings)) {
                $encoding = "BIG-5";
              }
              break;
            case 5:
              if (\array_key_exists("UHC", $system_encodings)) {
                $encoding = "UHC";
              }
              break;
          }
          break;
      }
      if ($encoding) {
        $glyphIndexArray = array();
        foreach ($_subtable["glyphIndexArray"] as $c => $gid) {
          $str = trim(pack("N", $c));
          if (\strlen($str) > 0) {
            $ord = $this->uniord($str, $encoding);
            if ($ord > 0) {
              $glyphIndexArray[$ord] = $gid;
            }
          }
        }
        return $glyphIndexArray;
      }
    }
    
    return null;
  }

  function setSubset($subset) {
    if (!is_array($subset)) {
      $subset = $this->utf8toUnicode($subset);
    }

    $subset = array_unique($subset);

    $glyphIndexArray = $this->getUnicodeCharMap();

    if (!$glyphIndexArray) {
      return;
    }

    $gids = array(
      0, // .notdef
      1, // .null
    );

    foreach ($subset as $code) {
      if (!isset($glyphIndexArray[$code])) {
        continue;
      }

      $gid        = $glyphIndexArray[$code];
      $gids[$gid] = $gid;
    }

    /** @var glyf $glyf */
    $glyf = $this->getTableObject("glyf");
    if ($glyf) {
      $gids = $glyf->getGlyphIDs($gids);
      sort($gids);
      $this->glyph_subset = $gids;
    }
    $this->glyph_all    = array_values($glyphIndexArray); // FIXME
  }

  function getSubset() {
    if (empty($this->glyph_subset)) {
      return $this->glyph_all;
    }

    return $this->glyph_subset;
  }

  function encode($tags = array()) {
    if (!self::$raw) {
      $tags = array_merge(array("head", "hhea", "cmap", "hmtx", "maxp", "glyf", "loca", "name", "post", "cvt ", "fpgm", "prep"), $tags);
    }
    else {
      $tags = array_keys($this->directory);
    }

    $n          = 16; // @todo

    Font::d("Tables : " . implode(", ", $tags));

    /** @var DirectoryEntry[] $entries */
    $entries = array();
    foreach ($tags as $tag) {
      if (!isset($this->directory[$tag])) {
        Font::d("  >> '$tag' table doesn't exist");
        continue;
      }

      $entries[$tag] = $this->directory[$tag];
    }

    $num_tables = count($entries);
    $exponent = floor(log($num_tables, 2));
    $power_of_two = pow(2, $exponent);

    $this->header->data["numTables"] = $num_tables;
    $this->header->data["searchRange"] = $power_of_two * 16;
    $this->header->data["entrySelector"] = log($power_of_two, 2);
    $this->header->data["rangeShift"] = $num_tables * 16 - $this->header->data["searchRange"];
    $this->header->encode();

    $directory_offset = $this->pos();
    $offset           = $directory_offset + $num_tables * $n;
    $this->seek($offset);

    $i = 0;
    foreach ($entries as $entry) {
      $entry->encode($directory_offset + $i * $n);
      $i++;
    }
  }

  function parseHeader() {
    if (!empty($this->header)) {
      return;
    }

    $this->seek($this->tableOffset);

    $this->header = new Header($this);
    $this->header->parse();
  }

  function getFontType(){
    $class_parts = explode("\\", get_class($this));
    return $class_parts[1];
  }

  function parseTableEntries() {
    $this->parseHeader();

    if (!empty($this->directory)) {
      return;
    }

    if (empty($this->header->data["numTables"])) {
      return;
    }


    $type = $this->getFontType();
    $class = "FontLib\\$type\\TableDirectoryEntry";

    for ($i = 0; $i < $this->header->data["numTables"]; $i++) {
      /** @var TableDirectoryEntry $entry */
      $entry = new $class($this);
      $entry->parse();

      $this->directory[$entry->tag] = $entry;
    }
  }

  function normalizeFUnit($value, $base = 1000) {
    return round($value * ($base / $this->getData("head", "unitsPerEm")));
  }

  protected function readTable($tag) {
    $this->parseTableEntries();

    if (!self::$raw) {
      $name_canon = preg_replace("/[^a-z0-9]/", "", strtolower($tag));

      $class = "FontLib\\Table\\Type\\$name_canon";

      if (!isset($this->directory[$tag]) || !@class_exists($class)) {
        return;
      }
    }
    else {
      $class = "FontLib\\Table\\Table";
    }

    /** @var Table $table */
    $table = new $class($this->directory[$tag]);
    $table->parse();

    $this->data[$tag] = $table;
  }

  /**
   * @param $name
   *
   * @return Table
   */
  public function getTableObject($name) {
    if (\array_key_exists($name, $this->data)) {
      return $this->data[$name];
    }
    return null;
  }

  public function setTableObject($name, Table $data) {
    $this->data[$name] = $data;
  }

  public function getData($name, $key = null) {
    $this->parseTableEntries();

    if (empty($this->data[$name])) {
      $this->readTable($name);
    }

    if (!isset($this->data[$name])) {
      return null;
    }

    if (!$key) {
      return $this->data[$name]->data;
    }
    else {
      return $this->data[$name]->data[$key];
    }
  }

  function addDirectoryEntry(DirectoryEntry $entry) {
    $this->directory[$entry->tag] = $entry;
  }

  function saveAdobeFontMetrics($file, $encoding = null) {
    $afm = new AdobeFontMetrics($this);
    $afm->write($file, $encoding);
  }

  /**
   * Get a specific name table string value from its ID
   *
   * @param int $nameID The name ID
   *
   * @return string|null
   */
  function getNameTableString($nameID) {
    /** @var nameRecord[] $records */
    $records = $this->getData("name", "records");

    if (!isset($records[$nameID])) {
      return null;
    }

    return $records[$nameID]->string;
  }

  /**
   * Get font copyright
   *
   * @return string|null
   */
  function getFontCopyright() {
    return $this->getNameTableString(name::NAME_COPYRIGHT);
  }

  /**
   * Get font name
   *
   * @return string|null
   */
  function getFontName() {
    return $this->getNameTableString(name::NAME_NAME);
  }

  /**
   * Get font subfamily
   *
   * @return string|null
   */
  function getFontSubfamily() {
    return $this->getNameTableString(name::NAME_SUBFAMILY);
  }

  /**
   * Get font subfamily ID
   *
   * @return string|null
   */
  function getFontSubfamilyID() {
    return $this->getNameTableString(name::NAME_SUBFAMILY_ID);
  }

  /**
   * Get font full name
   *
   * @return string|null
   */
  function getFontFullName() {
    return $this->getNameTableString(name::NAME_FULL_NAME);
  }

  /**
   * Get font version
   *
   * @return string|null
   */
  function getFontVersion() {
    return $this->getNameTableString(name::NAME_VERSION);
  }

  /**
   * Get font weight
   *
   * @return string|null
   */
  function getFontWeight() {
    return $this->getTableObject("OS/2")->data["usWeightClass"];
  }

  /**
   * Get font Postscript name
   *
   * @return string|null
   */
  function getFontPostscriptName() {
    return $this->getNameTableString(name::NAME_POSTSCRIPT_NAME);
  }

  function reduce() {
    $names_to_keep = array(
      name::NAME_COPYRIGHT,
      name::NAME_NAME,
      name::NAME_SUBFAMILY,
      name::NAME_SUBFAMILY_ID,
      name::NAME_FULL_NAME,
      name::NAME_VERSION,
      name::NAME_POSTSCRIPT_NAME,
    );

    foreach ($this->data["name"]->data["records"] as $id => $rec) {
      if (!in_array($id, $names_to_keep)) {
        unset($this->data["name"]->data["records"][$id]);
      }
    }
  }
}
