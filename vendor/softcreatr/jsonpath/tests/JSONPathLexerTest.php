<?php

/**
 * JSONPath implementation for PHP.
 *
 * @license https://github.com/SoftCreatR/JSONPath/blob/main/LICENSE  MIT License
 */

namespace Flow\JSONPath\Test;

use Flow\JSONPath\JSONPathException;
use Flow\JSONPath\JSONPathLexer;
use Flow\JSONPath\JSONPathToken;
use PHPUnit\Framework\Exception as PHPUnit_Framework_Exception;

class JSONPathLexerTest extends TestCase
{
    /**
     * @throws JSONPathException
     */
    public function testIndexWildcard()
    {
        $tokens = (new JSONPathLexer('.*'))
            ->parseExpression();

        self::assertEquals(JSONPathToken::T_INDEX, $tokens[0]->type);
        self::assertEquals("*", $tokens[0]->value);
    }

    /**
     * @throws JSONPathException
     */
    public function testIndexSimple()
    {
        $tokens = (new JSONPathLexer('.foo'))
            ->parseExpression();

        self::assertEquals(JSONPathToken::T_INDEX, $tokens[0]->type);
        self::assertEquals("foo", $tokens[0]->value);
    }

    /**
     * @throws JSONPathException
     */
    public function testIndexRecursive()
    {
        $tokens = (new JSONPathLexer('..teams.*'))
            ->parseExpression();

        self::assertCount(3, $tokens);
        self::assertEquals(JSONPathToken::T_RECURSIVE, $tokens[0]->type);
        self::assertEquals(null, $tokens[0]->value);
        self::assertEquals(JSONPathToken::T_INDEX, $tokens[1]->type);
        self::assertEquals('teams', $tokens[1]->value);
        self::assertEquals(JSONPathToken::T_INDEX, $tokens[2]->type);
        self::assertEquals('*', $tokens[2]->value);
    }

    /**
     * @throws JSONPathException
     */
    public function testIndexComplex()
    {
        $tokens = (new JSONPathLexer('["\'b.^*_"]'))
            ->parseExpression();

        self::assertEquals(JSONPathToken::T_INDEX, $tokens[0]->type);
        self::assertEquals("'b.^*_", $tokens[0]->value);
    }

    /**
     * @throws JSONPathException
     * @throws PHPUnit_Framework_Exception
     */
    public function testIndexBadlyFormed()
    {
        $this->expectException('Flow\JSONPath\JSONPathException');
        $this->expectExceptionMessage('Unable to parse token hello* in expression: .hello*');

        (new JSONPathLexer('.hello*'))
            ->parseExpression();
    }

    /**
     * @throws JSONPathException
     */
    public function testIndexInteger()
    {
        $tokens = (new JSONPathLexer('[0]'))
            ->parseExpression();

        self::assertEquals(JSONPathToken::T_INDEX, $tokens[0]->type);
        self::assertEquals("0", $tokens[0]->value);
    }

    /**
     * @throws JSONPathException
     */
    public function testIndexIntegerAfterDotNotation()
    {
        $tokens = (new JSONPathLexer('.books[0]'))
            ->parseExpression();

        self::assertEquals(JSONPathToken::T_INDEX, $tokens[0]->type);
        self::assertEquals(JSONPathToken::T_INDEX, $tokens[1]->type);
        self::assertEquals("books", $tokens[0]->value);
        self::assertEquals("0", $tokens[1]->value);
    }

    /**
     * @throws JSONPathException
     */
    public function testIndexWord()
    {
        $tokens = (new JSONPathLexer('["foo$-/\'"]'))
            ->parseExpression();

        self::assertEquals(JSONPathToken::T_INDEX, $tokens[0]->type);
        self::assertEquals("foo$-/'", $tokens[0]->value);
    }

    /**
     * @throws JSONPathException
     */
    public function testIndexWordWithWhitespace()
    {
        $tokens = (new JSONPathLexer('[   "foo$-/\'"     ]'))
            ->parseExpression();

        self::assertEquals(JSONPathToken::T_INDEX, $tokens[0]->type);
        self::assertEquals("foo$-/'", $tokens[0]->value);
    }

    /**
     * @throws JSONPathException
     */
    public function testSliceSimple()
    {
        $tokens = (new JSONPathLexer('[0:1:2]'))
            ->parseExpression();

        self::assertEquals(JSONPathToken::T_SLICE, $tokens[0]->type);
        self::assertEquals(['start' => 0, 'end' => 1, 'step' => 2], $tokens[0]->value);
    }

    /**
     * @throws JSONPathException
     */
    public function testIndexNegativeIndex()
    {
        $tokens = (new JSONPathLexer('[-1]'))
            ->parseExpression();

        self::assertEquals(JSONPathToken::T_SLICE, $tokens[0]->type);
        self::assertEquals(['start' => -1, 'end' => null, 'step' => null], $tokens[0]->value);
    }

    /**
     * @throws JSONPathException
     */
    public function testSliceAllNull()
    {
        $tokens = (new JSONPathLexer('[:]'))
            ->parseExpression();

        self::assertEquals(JSONPathToken::T_SLICE, $tokens[0]->type);
        self::assertEquals(['start' => null, 'end' => null, 'step' => null], $tokens[0]->value);
    }

    /**
     * @throws JSONPathException
     */
    public function testQueryResultSimple()
    {
        $tokens = (new JSONPathLexer('[(@.foo + 2)]'))
            ->parseExpression();

        self::assertEquals(JSONPathToken::T_QUERY_RESULT, $tokens[0]->type);
        self::assertEquals('@.foo + 2', $tokens[0]->value);
    }

    /**
     * @throws JSONPathException
     */
    public function testQueryMatchSimple()
    {
        $tokens = (new JSONPathLexer('[?(@.foo < \'bar\')]'))
            ->parseExpression();

        self::assertEquals(JSONPathToken::T_QUERY_MATCH, $tokens[0]->type);
        self::assertEquals('@.foo < \'bar\'', $tokens[0]->value);
    }

    /**
     * @throws JSONPathException
     */
    public function testQueryMatchNotEqualTO()
    {
        $tokens = (new JSONPathLexer('[?(@.foo != \'bar\')]'))
            ->parseExpression();

        self::assertEquals(JSONPathToken::T_QUERY_MATCH, $tokens[0]->type);
        self::assertEquals('@.foo != \'bar\'', $tokens[0]->value);
    }

    /**
     * @throws JSONPathException
     */
    public function testQueryMatchBrackets()
    {
        $tokens = (new JSONPathLexer("[?(@['@language']='en')]"))
            ->parseExpression();

        self::assertEquals(JSONPathToken::T_QUERY_MATCH, $tokens[0]->type);
        self::assertEquals("@['@language']='en'", $tokens[0]->value);
    }

    /**
     * @throws JSONPathException
     */
    public function testRecursiveSimple()
    {
        $tokens = (new JSONPathLexer('..foo'))
            ->parseExpression();

        self::assertEquals(JSONPathToken::T_RECURSIVE, $tokens[0]->type);
        self::assertEquals(JSONPathToken::T_INDEX, $tokens[1]->type);
        self::assertEquals(null, $tokens[0]->value);
        self::assertEquals('foo', $tokens[1]->value);
    }

    /**
     * @throws JSONPathException
     */
    public function testRecursiveWildcard()
    {
        $tokens = (new JSONPathLexer('..*'))
            ->parseExpression();

        self::assertEquals(JSONPathToken::T_RECURSIVE, $tokens[0]->type);
        self::assertEquals(JSONPathToken::T_INDEX, $tokens[1]->type);
        self::assertEquals(null, $tokens[0]->value);
        self::assertEquals('*', $tokens[1]->value);
    }

    /**
     * @throws JSONPathException
     * @throws PHPUnit_Framework_Exception
     */
    public function testRecursiveBadlyFormed()
    {
        $this->expectException('Flow\JSONPath\JSONPathException');
        $this->expectExceptionMessage('Unable to parse token ba^r in expression: ..ba^r');

        (new JSONPathLexer('..ba^r'))
            ->parseExpression();
    }

    /**
     * @throws JSONPathException
     */
    public function testIndexesSimple()
    {
        $tokens = (new JSONPathLexer('[1,2,3]'))
            ->parseExpression();

        self::assertEquals(JSONPathToken::T_INDEXES, $tokens[0]->type);
        self::assertEquals([1, 2, 3], $tokens[0]->value);
    }

    /**
     * @throws JSONPathException
     */
    public function testIndexesWhitespace()
    {
        $tokens = (new JSONPathLexer('[ 1,2 , 3]'))
            ->parseExpression();

        self::assertEquals(JSONPathToken::T_INDEXES, $tokens[0]->type);
        self::assertEquals([1, 2, 3], $tokens[0]->value);
    }
}
