<?php

declare(strict_types=1);

namespace Fig\Link\Builder;

const REGISTRY_FILE = 'https://www.iana.org/assignments/link-relations/link-relations.xml';

run();

function run() {
    $compiler = new RegistryCompiler();

    $records = new Records();
    $out = fopen('src/Relations.php', 'w');
    $compiler->compile($records(), $out);
}

class Records
{

    /**
     * Loads the XML object from the remote source.
     *
     * IANA requires a valid User-Agent string for all requests, and non-curl stream options for PHP seem to all
     * fail at sending a User-Agent, even when told to.  So we use the ugly way.
     */
    protected function getXml(): \SimpleXMLElement
    {
        $ch = curl_init(REGISTRY_FILE);
        curl_setopt($ch, CURLOPT_USERAGENT, 'php-fig/fig-utils');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $xml = curl_exec($ch);

        return simplexml_load_string($xml);
    }

    /**
     * Fetches the records for the relation registry.
     *
     * @return iterable<\SimpleXMLElement>
     */
    public function __invoke()
    {
        foreach ($this->getXml()->registry->children() as $element) {
            if ($element->getName() == 'record') {
                yield $element;
            }
        }
    }
}

/**
 * Compiles a list of SimpleXmlElements to a Relations interface.
 */
class RegistryCompiler
{
    /**
     * Writes a Relations index class based on the provided records.
     *
     * @param iterable<\SimpleXMLElement> $records
     *   An iterable of SimpleXml elements from the Link Relations XML file.
     * @param $stream
     *   An open file stream to which to write the generated code.
     * @param string $class
     *   The name of the interface to produce.
     * @param string $namespace
     *   The namespace of the interface to generate.
     */
    public function compile(iterable $records, $stream, string $class = 'Relations', string $namespace = 'Fig\\Link') : void
    {
        fwrite($stream, $this->createPreamble($class, $namespace));

        foreach ($records as $record) {
            $item = $this->createEntry($record);
            fwrite($stream, $item);
        }

        fwrite($stream, $this->createClosing());
    }

    /**
     * Processes a SimpleXml record into a constant definition.
     *
     * @param \SimpleXMLElement $record
     *   The record to process.
     * @return string
     */
    protected function createEntry(\SimpleXMLElement $record) : string
    {
        $value = (string)$record->value;
        $name = $this->buildName($value);
        $description = $this->rewrap((string)$record->description);
        $note = $this->rewrap((string)$record->note);
        $seeUri = $this->xrefLink($record->spec->xref);

        if ($note) {
            return <<<END

    /**
     * {$description}
     *
     * {$note}
     *
     * @see {$seeUri}
     */
    const REL_$name = '$value';

END;

        }
        else {
            return <<<END

    /**
     * {$description}
     *
     * @see {$seeUri}
     */
    const REL_$name = '$value';

END;

        }

    }

    /**
     * Maps an Xref element to a full URL.
     *
     * @param \SimpleXMLElement $xref
     *   The xref argument of a record.
     * @return string
     *   A web URL.
     * @throws \Exception
     */
    protected function xrefLink(\SimpleXMLElement $xref) : string
    {
        if ($xref['type'] == 'uri') {
            return (string)$xref['data'];
        }

        if ($xref['type'] == 'rfc') {
            return 'https://tools.ietf.org/html/' . $xref['data'];
        }

        // This seems to never happen.
        throw new \InvalidArgumentException("Unhandled type: {$xref['type']}");
    }

    /**
     * Fixes the word wrapping of a text string to fit in a docblock.
     *
     * @param string $text
     *   The string to rewrap.
     * @return string
     *   The rewrapped string.
     */
    protected function rewrap(string $text) : string
    {
        $text = preg_replace('/\s\s+/', ' ', $text);
        $text = wordwrap($text, 120);
        $text = str_replace("\n", "\n     * ", $text);
        return $text;
    }

    /**
     * Maps a rel name to the appropriate constant name.
     *
     * @param string $value
     *   The rel name from the record.
     * @return string
     *   A const-name-safe string.
     */
    protected function buildName(string $value) : string
    {
        return str_replace(['-', '.'], '_', strtoupper($value));
    }

    /**
     * Returns the opening of the file.
     *
     * @param string $class
     *   The name of the class/interface to generate.
     * @param string $namespace
     *   The namspace for the generated class.
     * @return string
     *   A portion of a valid PHP class file.
     */
    protected function createPreamble(string $class, string $namespace) : string
    {
        return <<<END
<?php

/**
 * Standard relation names.
 *
 * This file is auto-generated.  Do not edit directly.  Edit or re-run `rebuild-rels.php` if necessary.
 */

declare(strict_types=1);

namespace $namespace;

/**
 * Standard relation names.
 *
 * This interface provides convenience constants for standard relationships defined by IANA. They are not required,
 * but are useful for avoiding typos and similar such errors.
 *
 * This interface may be referenced directly like so:
 *
 * Relations::REL_UP
 *
 * Or you may implement this interface in your class and then refer to the constants locally:
 *
 * static::REL_UP
 */
interface $class
{
END;
    }

    /**
     * Returns the closing of a file.
     *
     * @return string
     */
    protected function createClosing() : string
    {
        return <<<'END'
}
END;
    }
}

