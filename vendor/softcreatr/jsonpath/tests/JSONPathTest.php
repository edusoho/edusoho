<?php

/**
 * JSONPath implementation for PHP.
 *
 * @license https://github.com/SoftCreatR/JSONPath/blob/main/LICENSE  MIT License
 */

namespace Flow\JSONPath\Test;

use Flow\JSONPath\JSONPath;
use Flow\JSONPath\JSONPathException;
use Flow\JSONPath\Test\Traits\TestDataTrait;

class JSONPathTest extends TestCase
{
    use TestDataTrait;

    /**
     * $.store.books[0].title
     *
     * @throws JSONPathException
     */
    public function testChildOperators()
    {
        $result = (new JSONPath($this->getData('example')))
            ->find('$.store.books[0].title');

        self::assertEquals('Sayings of the Century', $result[0]);
    }

    /**
     * @throws JSONPathException
     */
    public function testIndexesObject()
    {
        $result = (new JSONPath($this->getData('indexed-object')))
            ->find('$.store.books[3].title');

        self::assertEquals('Sword of Honour', $result[0]);
    }

    /**
     * $['store']['books'][0]['title']
     *
     * @throws JSONPathException
     */
    public function testChildOperatorsAlt()
    {
        $result = (new JSONPath($this->getData('example')))
            ->find("$['store']['books'][0]['title']");

        self::assertEquals('Sayings of the Century', $result[0]);
    }

    /**
     * $.array[start:end:step]
     *
     * @throws JSONPathException
     */
    public function testFilterSliceA()
    {
        // Copy all items... similar to a wildcard
        $result = (new JSONPath($this->getData('example')))
            ->find("$['store']['books'][:].title");

        self::assertEquals(
            ['Sayings of the Century', 'Sword of Honour', 'Moby Dick', 'The Lord of the Rings'],
            $result->getData()
        );
    }

    /**
     * Positive end indexes
     * $[0:2]
     *
     * @throws JSONPathException
     */
    public function testFilterSlicePositiveEndIndexes()
    {
        $jsonPath = (new JSONPath(['first', 'second', 'third', 'fourth', 'fifth']));

        $result = $jsonPath
            ->find('$[0:0]');

        self::assertEquals([], $result->getData());

        $result = $jsonPath
            ->find('$[0:1]');

        self::assertEquals(['first'], $result->getData());

        $result = $jsonPath
            ->find('$[0:2]');

        self::assertEquals(['first', 'second'], $result->getData());

        $result = $jsonPath
            ->find('$[:2]');

        self::assertEquals(['first', 'second'], $result->getData());

        $result = $jsonPath
            ->find('$[1:2]');

        self::assertEquals(['second'], $result->getData());

        $result = $jsonPath
            ->find('$[0:3:1]');

        self::assertEquals(['first', 'second', 'third'], $result->getData());

        $result = $jsonPath
            ->find('$[0:3:0]');

        self::assertEquals(['first', 'second', 'third'], $result->getData());
    }

    /**
     * @throws JSONPathException
     */
    public function testFilterSliceNegativeStartIndexes()
    {
        $result = (new JSONPath(['first', 'second', 'third', 'fourth', 'fifth']))
            ->find('$[-2:]');

        self::assertEquals(['fourth', 'fifth'], $result->getData());

        $result = (new JSONPath(['first', 'second', 'third', 'fourth', 'fifth']))
            ->find('$[-1:]');

        self::assertEquals(['fifth'], $result->getData());

        $result = (new JSONPath(['first', 'second', 'third']))
            ->find('$[-4:]');

        self::assertEquals(['first', 'second', 'third'], $result->getData());
    }

    /**
     * Negative end indexes
     * $[:-2]
     *
     * @throws JSONPathException
     */
    public function testFilterSliceNegativeEndIndexes()
    {
        $jsonPath = (new JSONPath(['first', 'second', 'third', 'fourth', 'fifth']));

        $result = $jsonPath
            ->find('$[:-2]');

        self::assertEquals(['first', 'second', 'third'], $result->getData());

        $result = $jsonPath
            ->find('$[0:-2]');

        self::assertEquals(['first', 'second', 'third'], $result->getData());
    }

    /**
     * Negative end indexes
     * $[:-2]
     *
     * @throws JSONPathException
     */
    public function testFilterSliceNegativeStartAndEndIndexes()
    {
        $jsonPath = (new JSONPath(['first', 'second', 'third', 'fourth', 'fifth']));

        $result = $jsonPath
            ->find('$[-2:-1]');

        self::assertEquals(['fourth'], $result->getData());

        $result = $jsonPath
            ->find('$[-4:-2]');

        self::assertEquals(['second', 'third'], $result->getData());
    }

    /**
     * Negative end indexes
     * $[:-2]
     *
     * @throws JSONPathException
     */
    public function testFilterSliceNegativeStartAndPositiveEnd()
    {
        $result = (new JSONPath(['first', 'second', 'third', 'fourth', 'fifth']))
            ->find('$[-2:2]');

        self::assertEquals([], $result->getData());
    }

    /**
     * @throws JSONPathException
     */
    public function testFilterSliceStepBy2()
    {
        $result = (new JSONPath(['first', 'second', 'third', 'fourth', 'fifth']))
            ->find('$[0:4:2]');

        self::assertEquals(['first', 'third'], $result->getData());
    }

    /**
     * The Last item
     * $[-1]
     *
     * @throws JSONPathException
     */
    public function testFilterLastIndex()
    {
        $result = (new JSONPath(['first', 'second', 'third', 'fourth', 'fifth']))
            ->find('$[-1]');

        self::assertEquals(['fifth'], $result->getData());
    }

    /**
     * Array index slice only end
     * $[:2]
     *
     * @throws JSONPathException
     */
    public function testFilterSliceG()
    {
        // Fetch up to the second index
        $result = (new JSONPath(['first', 'second', 'third', 'fourth', 'fifth']))
            ->find('$[:2]');

        self::assertEquals(['first', 'second'], $result->getData());
    }

    /**
     * $.store.books[(@.length-1)].title
     *
     * This notation is only partially implemented eg. hacked in
     *
     * @throws JSONPathException
     */
    public function testChildQuery()
    {
        $result = (new JSONPath($this->getData('example')))
            ->find('$.store.books[(@.length-1)].title');

        self::assertEquals(['The Lord of the Rings'], $result->getData());
    }

    /**
     * $.store.books[?(@.price < 10)].title
     * Filter books that have a price less than 10
     *
     * @throws JSONPathException
     */
    public function testQueryMatchLessThan()
    {
        $result = (new JSONPath($this->getData('example')))
            ->find('$.store.books[?(@.price < 10)].title');

        self::assertEquals(['Sayings of the Century', 'Moby Dick'], $result->getData());
    }

    /**
     * $.store.books[?(@.price > 10)].title
     * Filter books that have a price more than 10
     *
     * @throws JSONPathException
     */
    public function testQueryMatchMoreThan()
    {
        $result = (new JSONPath($this->getData('example')))
            ->find('$.store.books[?(@.price > 10)].title');

        self::assertEquals(['Sword of Honour', 'The Lord of the Rings'], $result->getData());
    }

    /**
     * $.store.books[?(@.price <= 12.99)].title
     * Filter books that have a price less or equal to 12.99
     *
     * @throws JSONPathException
     */
    public function testQueryMatchLessOrEqual()
    {
        $result = (new JSONPath($this->getData('example')))
            ->find('$.store.books[?(@.price <= 12.99)].title');

        self::assertEquals(['Sayings of the Century', 'Sword of Honour', 'Moby Dick'], $result->getData());
    }

    /**
     * $.store.books[?(@.price >= 12.99)].title
     * Filter books that have a price less or equal to 12.99
     *
     * @throws JSONPathException
     */
    public function testQueryMatchEqualOrMore()
    {
        $result = (new JSONPath($this->getData('example')))
            ->find('$.store.books[?(@.price >= 12.99)].title');

        self::assertEquals(['Sword of Honour', 'The Lord of the Rings'], $result->getData());
    }

    /**
     * $..books[?(@.author == "J. R. R. Tolkien")]
     * Filter books that have an author equal to "..."
     *
     * @throws JSONPathException
     */
    public function testQueryMatchEquals()
    {
        $result = (new JSONPath($this->getData('example')))
            ->find('$..books[?(@.author == "J. R. R. Tolkien")].title');

        self::assertEquals('The Lord of the Rings', $result[0]);
    }

    /**
     * $..books[?(@.author = 1)]
     * Filter books that have a title equal to "..."
     *
     * @throws JSONPathException
     */
    public function testQueryMatchEqualsWithUnquotedInteger()
    {
        $results = (new JSONPath($this->getData('simple-integers')))
            ->find('$..features[?(@.value = 1)]');

        self::assertEquals('foo', $results[0]->name);
        self::assertEquals('baz', $results[1]->name);
    }

    /**
     * $..books[?(@.author != "J. R. R. Tolkien")]
     * Filter books that have an author not equal to "..."
     *
     * @throws JSONPathException
     */
    public function testQueryMatchNotEqualsTo()
    {
        $jsonPath = (new JSONPath($this->getData('example')));

        $results = $jsonPath
            ->find('$..books[?(@.author != "J. R. R. Tolkien")].title');

        self::assertcount(3, $results);
        self::assertEquals(['Sayings of the Century', 'Sword of Honour', 'Moby Dick'], $results->getData());

        $results = $jsonPath
            ->find('$..books[?(@.author !== "J. R. R. Tolkien")].title');

        self::assertcount(3, $results);
        self::assertEquals(['Sayings of the Century', 'Sword of Honour', 'Moby Dick'], $results->getData());

        $results = $jsonPath
            ->find('$..books[?(@.author <> "J. R. R. Tolkien")].title');

        self::assertcount(3, $results);
        self::assertEquals(['Sayings of the Century', 'Sword of Honour', 'Moby Dick'], $results->getData());
    }

    /**
     * $..books[?(@.author =~ /nigel ree?s/i)]
     * Filter books where author matches regex
     *
     * @throws JSONPathException
     */
    public function testQueryMatchWithRegexCaseSensitive()
    {
        $jsonPath = (new JSONPath($this->getData('example')));

        $results = $jsonPath
            ->find('$..books[?(@.author =~ /nigel ree?s/i)].title');

        self::assertcount(1, $results);
        self::assertEquals(['Sayings of the Century'], $results->getData());

        $results = $jsonPath
            ->find('$..books[?(@.title =~ /^(Say|The).*/)].title');

        self::assertcount(2, $results);
        self::assertEquals(['Sayings of the Century', 'The Lord of the Rings'], $results->getData());
    }

    /**
     * $..books[?(@.author =~ "J. R. R. Tolkien")]
     * Filter books where author matches invalid regex
     *
     * @throws JSONPathException
     */
    public function testQueryMatchWithInvalidRegex()
    {
        $result = (new JSONPath($this->getData('example')))
            ->find('$..books[?(@.author =~ "J. R. R. Tolkien")].title');

        self::assertEmpty($result->getData());
    }

    /**
     * $..books[?(@.author in ["J. R. R. Tolkien", "Nigel Rees"])]
     * Filter books that have a title in ["...", "..."]
     *
     * @throws JSONPathException
     */
    public function testQueryMatchIn()
    {
        $results = (new JSONPath($this->getData('example')))
            ->find('$..books[?(@.author in ["J. R. R. Tolkien", "Nigel Rees"])].title');

        self::assertEquals(['Sayings of the Century', 'The Lord of the Rings'], $results->getData());
    }

    /**
     * $..books[?(@.author nin ["J. R. R. Tolkien", "Nigel Rees"])]
     * Filter books that don't have a title in ["...", "..."]
     *
     * @throws JSONPathException
     */
    public function testQueryMatchNin()
    {
        $results = (new JSONPath($this->getData('example')))
            ->find('$..books[?(@.author nin ["J. R. R. Tolkien", "Nigel Rees"])].title');

        self::assertEquals(['Sword of Honour', 'Moby Dick'], $results->getData());
    }

    /**
     * $..books[?(@.author nin ["J. R. R. Tolkien", "Nigel Rees"])]
     * Filter books that don't have a title in ["...", "..."]
     *
     * @throws JSONPathException
     */
    public function testQueryMatchNotIn()
    {
        $results = (new JSONPath($this->getData('example')))
            ->find('$..books[?(@.author !in ["J. R. R. Tolkien", "Nigel Rees"])].title');

        self::assertEquals(['Sword of Honour', 'Moby Dick'], $results->getData());
    }

    /**
     * $.store.books[*].author
     *
     * @throws JSONPathException
     */
    public function testWildcardAltNotation()
    {
        $results = (new JSONPath($this->getData('example')))
            ->find('$.store.books[*].author');

        self::assertEquals(['Nigel Rees', 'Evelyn Waugh', 'Herman Melville', 'J. R. R. Tolkien'], $results->getData());
    }

    /**
     * $..author
     *
     * @throws JSONPathException
     */
    public function testRecursiveChildSearch()
    {
        $result = (new JSONPath($this->getData('example')))
            ->find('$..author');

        self::assertEquals(['Nigel Rees', 'Evelyn Waugh', 'Herman Melville', 'J. R. R. Tolkien'], $result->getData());
    }

    /**
     * $.store.*
     * all things in store
     * the structure of the example data makes this test look weird
     *
     * @throws JSONPathException
     */
    public function testWildCard()
    {
        $result = (new JSONPath($this->getData('example')))
            ->find('$.store.*');

        if (is_object($result[0][0])) {
            self::assertEquals('Sayings of the Century', $result[0][0]->title);
        } else {
            self::assertEquals('Sayings of the Century', $result[0][0]['title']);
        }

        if (is_object($result[1])) {
            self::assertEquals('red', $result[1]->color);
        } else {
            self::assertEquals('red', $result[1]['color']);
        }
    }

    /**
     * $.store..price
     * the price of everything in the store.
     *
     * @throws JSONPathException
     */
    public function testRecursiveChildSearchAlt()
    {
        $result = (new JSONPath($this->getData('example')))
            ->find('$.store..price');

        self::assertEquals([8.95, 12.99, 8.99, 22.99, 19.95], $result->getData());
    }

    /**
     * $..books[2]
     * the third book
     *
     * @throws JSONPathException
     */
    public function testRecursiveChildSearchWithChildIndex()
    {
        $result = (new JSONPath($this->getData('example')))
            ->find('$..books[2].title');

        self::assertEquals(['Moby Dick'], $result->getData());
    }

    /**
     * $..books[(@.length-1)]
     *
     * @throws JSONPathException
     */
    public function testRecursiveChildSearchWithChildQuery()
    {
        $result = (new JSONPath($this->getData('example')))
            ->find('$..books[(@.length-1)].title');

        self::assertEquals(['The Lord of the Rings'], $result->getData());
    }

    /**
     * $..books[-1:]
     * Return the last results
     *
     * @throws JSONPathException
     */
    public function testRecursiveChildSearchWithSliceFilter()
    {
        $result = (new JSONPath($this->getData('example')))
            ->find('$..books[-1:].title');

        self::assertEquals(['The Lord of the Rings'], $result->getData());
    }

    /**
     * $..books[?(@.isbn)]
     * filter all books with isbn number
     *
     * @throws JSONPathException
     */
    public function testRecursiveWithQueryMatch()
    {
        $result = (new JSONPath($this->getData('example')))
            ->find('$..books[?(@.isbn)].isbn');

        self::assertEquals(['0-553-21311-3', '0-395-19395-8'], $result->getData());
    }

    /**
     * .data.tokens[?(@.Employee.FirstName)]
     * Verify that it is possible to filter with a key containing punctuation
     *
     * @throws JSONPathException
     */
    public function testRecursiveWithQueryMatchWithDots()
    {
        $result = (new JSONPath($this->getData('with-dots')))
            ->find(".data.tokens[?(@.Employee.FirstName)]");
        $result = json_decode(json_encode($result), true);

        self::assertEquals([['Employee.FirstName' => 'Jack']], $result);
    }

    /**
     * $..*
     * All members of JSON structure
     *
     * @throws JSONPathException
     */
    public function testRecursiveWithWildcard()
    {
        $result = (new JSONPath($this->getData('example')))
            ->find('$..*');
        $result = json_decode(json_encode($result), true);

        self::assertEquals('Sayings of the Century', $result[0]['books'][0]['title']);
        self::assertEquals(19.95, $result[27]);
    }

    /**
     * Tests direct key access.
     *
     * @throws JSONPathException
     */
    public function testSimpleArrayAccess()
    {
        $result = (new JSONPath(['title' => 'test title']))
            ->find('title');

        self::assertEquals(['test title'], $result->getData());
    }

    /**
     * @throws JSONPathException
     */
    public function testFilteringOnNoneArrays()
    {
        $result = (new JSONPath(['foo' => 'asdf']))
            ->find('$.foo.bar');

        self::assertEquals([], $result->getData());
    }

    /**
     * @throws JSONPathException
     */
    public function testMagicMethods()
    {
        $fooClass = new JSONPathTestClass();
        $results = (new JSONPath($fooClass, JSONPath::ALLOW_MAGIC))->find('$.foo');

        self::assertEquals(['bar'], $results->getData());
    }

    /**
     * @throws JSONPathException
     */
    public function testMatchWithComplexSquareBrackets()
    {
        $result = (new JSONPath($this->getData('extra')))
            ->find("$['http://www.w3.org/2000/01/rdf-schema#label'][?(@['@language']='en')]['@language']");

        self::assertEquals(["en"], $result->getData());
    }

    /**
     * @throws JSONPathException
     */
    public function testQueryMatchWithRecursive()
    {
        $result = (new JSONPath($this->getData('locations')))
            ->find("..[?(@.type == 'suburb')].name");

        self::assertEquals(["Rosebank"], $result->getData());
    }

    /**
     * @throws JSONPathException
     */
    public function testFirst()
    {
        $result = (new JSONPath($this->getData('extra')))
            ->find("$['http://www.w3.org/2000/01/rdf-schema#label'].*");

        self::assertEquals(["@language" => "en"], $result->first()->getData());
    }

    /**
     * @throws JSONPathException
     */
    public function testLast()
    {
        $result = (new JSONPath($this->getData('extra')))
            ->find("$['http://www.w3.org/2000/01/rdf-schema#label'].*");

        self::assertEquals(["@language" => "de"], $result->last()->getData());
    }

    /**
     * @throws JSONPathException
     */
    public function testSlashesInIndex()
    {
        $result = (new JSONPath($this->getData('with-slashes')))
            ->find("$['mediatypes']['image/png']");

        self::assertEquals(["/core/img/filetypes/image.png"], $result->getData());
    }

    /**
     * @throws JSONPathException
     */
    public function testUnionWithKeys()
    {
        $result = (new JSONPath(
            [
                "key" => "value",
                "another" => "entry",
            ]
        ))->find("$['key','another']");

        self::assertEquals(["value", "entry"], $result->getData());
    }

    /**
     * @throws JSONPathException
     */
    public function testCyrillicText()
    {
        $jsonPath = (new JSONPath(["трололо" => 1]));

        $result = $jsonPath
            ->find("$['трололо']");

        self::assertEquals([1], $result->getData());

        $result = $jsonPath
            ->find("$.трололо");

        self::assertEquals([1], $result->getData());
    }

    public function testOffsetUnset()
    {
        $jsonIterator = new JSONPath(
            [
                "route" => [
                    ["name" => "A", "type" => "type of A"],
                    ["name" => "B", "type" => "type of B"],
                ],
            ]
        );

        /** @var JSONPath $route */
        $route = $jsonIterator->offsetGet('route');
        $route->offsetUnset(0);
        $first = $route->first();

        self::assertEquals("B", $first['name']);
    }

    public function testFirstKey()
    {
        // Array test for array
        $firstKey = (new JSONPath(['a' => 'A', 'b', 'B']))->firstKey();

        self::assertEquals('a', $firstKey);

        // Array test for object
        $firstKey = (new JSONPath((object)['a' => 'A', 'b', 'B']))->firstKey();

        self::assertEquals('a', $firstKey);
    }

    public function testLastKey()
    {
        // Array test for array
        $lastKey = (new JSONPath(['a' => 'A', 'b' => 'B', 'c' => 'C']))->lastKey();

        self::assertEquals('c', $lastKey);

        // Array test for object
        $lastKey = (new JSONPath((object)['a' => 'A', 'b' => 'B', 'c' => 'C']))->lastKey();

        self::assertEquals('c', $lastKey);
    }

    /**
     * Test: ensure trailing comma is stripped during parsing
     *
     * @throws JSONPathException
     */
    public function testTrailingComma()
    {
        $result = (new JSONPath($this->getData('example')))
            ->find("$..books[0,1,2,]");

        self::assertCount(3, $result);
    }

    /**
     * Test: ensure negative indexes return -n from last index
     *
     * @throws JSONPathException
     */
    public function testNegativeIndex()
    {
        $result = (new JSONPath($this->getData('example')))
            ->find('$..books[-2]');

        self::assertEquals("Herman Melville", $result[0]['author']);
    }

    /**
     * @throws JSONPathException
     */
    public function testQueryAccessWithNumericalIndexes()
    {
        $result = (new JSONPath($this->getData('numerical-indexes-object')))
            ->find("$.result.list[?(@.o == \"11.51000\")]");

        self::assertEquals("11.51000", $result[0]->o);

        $result = (new JSONPath($this->getData('numerical-indexes-array')))
            ->find("$.result.list[?(@[1] == \"11.51000\")]");

        self::assertEquals("11.51000", $result[0][1]);
    }
}
