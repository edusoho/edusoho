<?php

/**
 * JSONPath implementation for PHP.
 *
 * @license https://github.com/SoftCreatR/JSONPath/blob/main/LICENSE  MIT License
 */

declare(strict_types=1);

namespace Flow\JSONPath\Test;

use Flow\JSONPath\{JSONPath, JSONPathException};
use PHPUnit\Framework\{ExpectationFailedException, TestCase};
use RuntimeException;

use function fwrite;
use function json_decode;
use function json_encode;

use const STDERR;

class QueryTest extends TestCase
{
    /**
     * This method aims to test the current implementation against
     * all queries listed on https://cburgmer.github.io/json-path-comparison/
     *
     * Every test performed is allowed to fail and whenever an assertion fails,
     * a message will be printed to STDERR, so we know, what's going on.
     *
     * @see https://cburgmer.github.io/json-path-comparison
     * @dataProvider queryDataProvider
     */
    public function testQueries(
        string $id,
        string $selector,
        string $data,
        string $consensus,
        bool $skip = false
    ): void {
        $results = null;
        $query = ucwords(str_replace('_', ' ', $id));
        $url = sprintf('https://cburgmer.github.io/json-path-comparison/results/%s', $id);

        // Avoid "This test did not perform any assertions"
        // but do not use markTestSkipped, to prevent unnecessary
        // console outputs
        self::assertTrue(true);

        if (empty($consensus) || $skip) {
            /*$skipReason = empty($consensus) ? 'unknown consensus' : 'skip flag set';

            fwrite(STDERR, "==========================\n");
            fwrite(STDERR, "Query: {$query}\nSKIPPED ({$skipReason})\nMore information: {$url}\n");
            fwrite(STDERR, "==========================\n\n");*/

            return;
        }

        try {
            $results = json_encode((new JSONPath(json_decode($data, true)))->find($selector));

            self::assertEquals($consensus, $results);
        } catch (ExpectationFailedException $e) {
            try {
                // In some cases, the consensus is just disordered, while
                // the actual result is correct. Let's perform a canonicalized
                // assert in these cases. There might be still some false positives
                // (e.g. multidimensional comparisons), but that's okay, I guess. Maybe,
                // we can also find a way around that in the future.
                self::assertEqualsCanonicalizing(
                    json_decode($consensus, true),
                    json_decode($results, true)
                );
            } catch (ExpectationFailedException $f) {
                $e = $e->getComparisonFailure();

                fwrite(STDERR, "==========================\n");
                fwrite(STDERR, "Query: {$query}\n\n{$e->toString()}\nMore information: $url\n");
                fwrite(STDERR, "==========================\n\n");
            }
        } catch (JSONPathException $e) {
            fwrite(STDERR, "==========================\n");
            fwrite(STDERR, "Query: {$query}\n\n{$e->getMessage()}\n");
            fwrite(STDERR, "==========================\n\n");
        } catch (RuntimeException $e) {
            // ignore
        }
    }

    /**
     * Returns a list of queries, test data and expected results.
     *
     * A hand full of queries may run forever, thus they should
     * be skipped for now.
     *
     * Queries that are currently known as "problematic" are:
     *
     * - array_slice_with_negative_step_and_start_greater_than_end
     * - array_slice_with_open_end_and_negative_step
     * - array_slice_with_large_number_for_start
     * - array_slice_with_large_number_for_end
     * - array_slice_with_open_start_and_negative_step
     * - array_slice_with_negative_step_only
     *
     * The list is generated automatically, based on the results
     * at https://cburgmer.github.io/json-path-comparison.
     *
     * @return string[]
     */
    public function queryDataProvider(): array
    {
        return [
            [ // data set #0
                'array_slice',
                '$[1:3]',
                '["first","second","third","forth","fifth"]',
                '["second","third"]'
            ],
            [ // data set #1
                'array_slice_on_exact_match',
                '$[0:5]',
                '["first","second","third","forth","fifth"]',
                '["first","second","third","forth","fifth"]'
            ],
            [ // data set #2
                'array_slice_on_non_overlapping_array',
                '$[7:10]',
                '["first","second","third"]',
                '[]'
            ],
            [ // data set #3
                'array_slice_on_object',
                '$[1:3]',
                '{":":42,"more":"string","a":1,"b":2,"c":3,"1:3":"nice"}',
                '[]'
            ],
            [ // data set #4
                'array_slice_on_partially_overlapping_array',
                '$[1:10]',
                '["first","second","third"]',
                '["second","third"]'
            ],
            [ // data set #5
                'array_slice_with_large_number_for_end',
                '$[2:113667776004]',
                '["first","second","third","forth","fifth"]',
                '["third","forth","fifth"]',
                true, // skip
            ],
            [ // data set #6 - unknown consensus, fallback to Proposal A
                'array_slice_with_large_number_for_end_and_negative_step',
                '$[2:-113667776004:-1]',
                '["first","second","third","forth","fifth"]',
                '["third","second","first"]'
            ],
            [ // data set #7
                'array_slice_with_large_number_for_start',
                '$[-113667776004:2]',
                '["first","second","third","forth","fifth"]',
                '["first","second"]',
                true, // skip
            ],
            [ // data set #8 - unknown consensus, fallback to Proposal A
                'array_slice_with_large_number_for_start_end_negative_step',
                '$[113667776004:2:-1]',
                '["first","second","third","forth","fifth"]',
                '["fifth","forth"]'
            ],
            [ // data set #9
                'array_slice_with_negative_start_and_end_and_range_of_-1',
                '$[-4:-5]',
                '[2,"a",4,5,100,"nice"]',
                '[]'
            ],
            [ // data set #10
                'array_slice_with_negative_start_and_end_and_range_of_0',
                '$[-4:-4]',
                '[2,"a",4,5,100,"nice"]',
                '[]'
            ],
            [ // data set #11
                'array_slice_with_negative_start_and_end_and_range_of_1',
                '$[-4:-3]',
                '[2,"a",4,5,100,"nice"]',
                '[4]'
            ],
            [ // data set #12
                'array_slice_with_negative_start_and_positive_end_and_range_of_-1',
                '$[-4:1]',
                '[2,"a",4,5,100,"nice"]',
                '[]'
            ],
            [ // data set #13
                'array_slice_with_negative_start_and_positive_end_and_range_of_0',
                '$[-4:2]',
                '[2,"a",4,5,100,"nice"]',
                '[]'
            ],
            [ // data set #14
                'array_slice_with_negative_start_and_positive_end_and_range_of_1',
                '$[-4:3]',
                '[2,"a",4,5,100,"nice"]',
                '[4]'
            ],
            [ // data set #15 - unknown consensus, fallback to Proposal A
                'array_slice_with_negative_step',
                '$[3:0:-2]',
                '["first","second","third","forth","fifth"]',
                '["forth","second"]'
            ],
            [ // data set #16 - unknown consensus, fallback to Proposal A
                'array_slice_with_negative_step_and_start_greater_than_end',
                '$[0:3:-2]',
                '["first","second","third","forth","fifth"]',
                '[]',
                true, // skip
            ],
            [ // data set #17 - unknown consensus, fallback to Proposal A
                'array_slice_with_negative_step_on_partially_overlapping_array',
                '$[7:3:-1]',
                '["first","second","third","forth","fifth"]',
                '["fifth"]'
            ],
            [ // data set #18 - unknown consensus, fallback to Proposal A
                'array_slice_with_negative_step_only',
                '$[::-2]',
                '["first","second","third","forth","fifth"]',
                '["fifth","third","first"]',
                true, // skip
            ],
            [ // data set #19
                'array_slice_with_open_end',
                '$[1:]',
                '["first","second","third","forth","fifth"]',
                '["second","third","forth","fifth"]'
            ],
            [ // data set #20 - unknown consensus, fallback to Proposal A
                'array_slice_with_open_end_and_negative_step',
                '$[3::-1]',
                '["first","second","third","forth","fifth"]',
                '["forth","third","second","first"]',
                true, // skip
            ],
            [ // data set #21
                'array_slice_with_open_start',
                '$[:2]',
                '["first","second","third","forth","fifth"]',
                '["first","second"]'
            ],
            [ // data set #22
                'array_slice_with_open_start_and_end',
                '$[:]',
                '["first","second"]',
                '["first","second"]'
            ],
            [ // data set #23
                'array_slice_with_open_start_and_end_and_step_empty',
                '$[::]',
                '["first","second"]',
                '["first","second"]'
            ],
            [ // data set #24 - unknown consensus, fallback to Proposal A
                'array_slice_with_open_start_and_end_on_object',
                '$[:]',
                '{":":42,"more":"string"}',
                '[]'
            ],
            [ // data set #25 - unknown consensus, fallback to Proposal A
                'array_slice_with_open_start_and_negative_step',
                '$[:2:-1]',
                '["first","second","third","forth","fifth"]',
                '["fifth","forth"]',
                true, // skip
            ],
            [ // data set #26
                'array_slice_with_positive_start_and_negative_end_and_range_of_-1',
                '$[3:-4]',
                '[2,"a",4,5,100,"nice"]',
                '[]'
            ],
            [ // data set #27
                'array_slice_with_positive_start_and_negative_end_and_range_of_0',
                '$[3:-3]',
                '[2,"a",4,5,100,"nice"]',
                '[]'
            ],
            [ // data set #28
                'array_slice_with_positive_start_and_negative_end_and_range_of_1',
                '$[3:-2]',
                '[2,"a",4,5,100,"nice"]',
                '[5]'
            ],
            [ // data set #29
                'array_slice_with_range_of_-1',
                '$[2:1]',
                '["first","second","third","forth"]',
                '[]'
            ],
            [ // data set #30
                'array_slice_with_range_of_0',
                '$[0:0]',
                '["first","second"]',
                '[]'
            ],
            [ // data set #31
                'array_slice_with_range_of_1',
                '$[0:1]',
                '["first","second"]',
                '["first"]'
            ],
            [ // data set #32
                'array_slice_with_start_-1_and_open_end',
                '$[-1:]',
                '["first","second","third"]',
                '["third"]'
            ],
            [ // data set #33
                'array_slice_with_start_-2_and_open_end',
                '$[-2:]',
                '["first","second","third"]',
                '["second","third"]'
            ],
            [ // data set #34
                'array_slice_with_start_large_negative_number_and_open_end_on_short_array',
                '$[-4:]',
                '["first","second","third"]',
                '["first","second","third"]'
            ],
            [ // data set #35
                'array_slice_with_step',
                '$[0:3:2]',
                '["first","second","third","forth","fifth"]',
                '["first","third"]'
            ],
            [ // data set #36 - unknown consensus
                'array_slice_with_step_0',
                '$[0:3:0]',
                '["first","second","third","forth","fifth"]',
                ''
            ],
            [ // data set #37
                'array_slice_with_step_1',
                '$[0:3:1]',
                '["first","second","third","forth","fifth"]',
                '["first","second","third"]'
            ],
            [ // data set #38
                'array_slice_with_step_and_leading_zeros',
                '$[010:024:010]',
                '[0,1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25]',
                '[10,20]'
            ],
            [ // data set #39
                'array_slice_with_step_but_end_not_aligned',
                '$[0:4:2]',
                '["first","second","third","forth","fifth"]',
                '["first","third"]'
            ],
            [ // data set #40
                'array_slice_with_step_empty',
                '$[1:3:]',
                '["first","second","third","forth","fifth"]',
                '["second","third"]'
            ],
            [ // data set #41
                'array_slice_with_step_only',
                '$[::2]',
                '["first","second","third","forth","fifth"]',
                '["first","third","fifth"]'
            ],
            [ // data set #42
                'bracket_notation',
                '$[\'key\']',
                '{"key":"value"}',
                '["value"]'
            ],
            [ // data set #43
                'bracket_notation_after_recursive_descent',
                '$..[0]',
                '["first",{"key":["first nested",{"more":[{"nested":["deepest","second"]},["more","values"]]}]}]',
                '["deepest","first nested","first","more",{"nested":["deepest","second"]}]'
            ],
            [ // data set #44
                'bracket_notation_on_object_without_key',
                '$[\'missing\']',
                '{"key":"value"}',
                '[]'
            ],
            [ // data set #45
                'bracket_notation_with_NFC_path_on_NFD_key',
                '$[\'ü\']',
                '{"u\\u0308":42}',
                '[]'
            ],
            [ // data set #46
                'bracket_notation_with_dot',
                '$[\'two.some\']',
                '{"one":{"key":"value"},"two":{"some":"more","key":"other value"},"two.some":"42"}',
                '["42"]'
            ],
            [ // data set #47
                'bracket_notation_with_double_quotes',
                '$["key"]',
                '{"key":"value"}',
                '["value"]'
            ],
            [ // data set #48 - unknown consensus
                'bracket_notation_with_empty_path',
                '$[]',
                '{"":42,"\'\'":123,"\\"\\"":222}',
                ''
            ],
            [ // data set #49
                'bracket_notation_with_empty_string',
                '$[\'\']',
                '{"":42,"\'\'":123,"\\"\\"":222}',
                '[42]'
            ],
            [ // data set #50
                'bracket_notation_with_empty_string_doubled_quoted',
                '$[""]',
                '{"":42,"\'\'":123,"\\"\\"":222}',
                '[42]'
            ],
            [ // data set #51
                'bracket_notation_with_negative_number_on_short_array',
                '$[-2]',
                '["one element"]',
                '[]'
            ],
            [ // data set #52
                'bracket_notation_with_number',
                '$[2]',
                '["first","second","third","forth","fifth"]',
                '["third"]'
            ],
            [ // data set #53
                'bracket_notation_with_number_-1',
                '$[-1]',
                '["first","second","third"]',
                '["third"]'
            ],
            [ // data set #54
                'bracket_notation_with_number_-1_on_empty_array',
                '$[-1]',
                '[]',
                '[]'
            ],
            [ // data set #55
                'bracket_notation_with_number_0',
                '$[0]',
                '["first","second","third","forth","fifth"]',
                '["first"]'
            ],
            [ // data set #56
                'bracket_notation_with_number_after_dot_notation_with_wildcard_on_nested_arrays_with_different_length',
                '$.*[1]',
                '[[1],[2,3]]',
                '[3]'
            ],
            [ // data set #57 - unknown consensus, fallback to Proposal A
                'bracket_notation_with_number_on_object',
                '$[0]',
                '{"0":"value"}',
                '[]'
            ],
            [ // data set #58
                'bracket_notation_with_number_on_short_array',
                '$[1]',
                '["one element"]',
                '[]'
            ],
            [ // data set #59 - unknown consensus, fallback to Proposal A
                'bracket_notation_with_number_on_string',
                '$[0]',
                '"Hello World"',
                '[]'
            ],
            [ // data set #60
                'bracket_notation_with_quoted_array_slice_literal',
                '$[\':\']',
                '{":":"value","another":"entry"}',
                '["value"]'
            ],
            [ // data set #61
                'bracket_notation_with_quoted_closing_bracket_literal',
                '$[\']\']',
                '{"]":42}',
                '[42]'
            ],
            [ // data set #62
                'bracket_notation_with_quoted_current_object_literal',
                '$[\'@\']',
                '{"@":"value","another":"entry"}',
                '["value"]'
            ],
            [ // data set #63
                'bracket_notation_with_quoted_dot_literal',
                '$[\'.\']',
                '{".":"value","another":"entry"}',
                '["value"]'
            ],
            [ // data set #64
                'bracket_notation_with_quoted_dot_wildcard',
                '$[\'.*\']',
                '{"key":42,".*":1,"":10}',
                '[1]'
            ],
            [ // data set #65
                'bracket_notation_with_quoted_double_quote_literal',
                '$[\'"\']',
                '{"\\"":"value","another":"entry"}',
                '["value"]'
            ],
            [ // data set #66 - unknown consensus, fallback to Proposal A
                'bracket_notation_with_quoted_escaped_backslash',
                '$[\'\\\\\']',
                '{"\\\\":"value"}',
                '["value"]'
            ],
            [ // data set #67 - unknown consensus, fallback to Proposal A
                'bracket_notation_with_quoted_escaped_single_quote',
                '$[\'\\\'\']',
                '{"\'":"value"}',
                '["value"]'
            ],
            [ // data set #68
                'bracket_notation_with_quoted_number_on_object',
                '$[\'0\']',
                '{"0":"value"}',
                '["value"]'
            ],
            [ // data set #69
                'bracket_notation_with_quoted_root_literal',
                '$[\'$\']',
                '{"$":"value","another":"entry"}',
                '["value"]'
            ],
            [ // data set #70 - unknown consensus, fallback to Proposal A
                'bracket_notation_with_quoted_special_characters_combined',
                '$[\':@."$,*\\\'\\\\\']',
                '{":@.\\"$,*\'\\\\":42}',
                '[42]'
            ],
            [ // data set #71 - unknown consensus
                'bracket_notation_with_quoted_string_and_unescaped_single_quote',
                '$[\'single\'quote\']',
                '{"single\'quote":"value"}',
                ''
            ],
            [ // data set #72
                'bracket_notation_with_quoted_union_literal',
                '$[\',\']',
                '{",":"value","another":"entry"}',
                '["value"]'
            ],
            [ // data set #73
                'bracket_notation_with_quoted_wildcard_literal',
                '$[\'*\']',
                '{"*":"value","another":"entry"}',
                '["value"]'
            ],
            [ // data set #74 - unknown consensus, fallback to Proposal A
                'bracket_notation_with_quoted_wildcard_literal_on_object_without_key',
                '$[\'*\']',
                '{"another":"entry"}',
                '[]'
            ],
            [ // data set #75
                'bracket_notation_with_string_including_dot_wildcard',
                '$[\'ni.*\']',
                '{"nice":42,"ni.*":1,"mice":100}',
                '[1]'
            ],
            [ // data set #76 - unknown consensus
                'bracket_notation_with_two_literals_separated_by_dot',
                '$[\'two\'.\'some\']',
                '{"one":{"key":"value"},"two":{"some":"more","key":"other value"},"two.some":"42","two\'.\'some":"43' .
                '"}',
                ''
            ],
            [ // data set #77 - unknown consensus
                'bracket_notation_with_two_literals_separated_by_dot_without_quotes',
                '$[two.some]',
                '{"one":{"key":"value"},"two":{"some":"more","key":"other value"},"two.some":"42"}',
                ''
            ],
            [ // data set #78
                'bracket_notation_with_wildcard_after_array_slice',
                '$[0:2][*]',
                '[[1,2],["a","b"],[0,0]]',
                '[1,2,"a","b"]'
            ],
            [ // data set #79
                'bracket_notation_with_wildcard_after_dot_notation_after_bracket_notation_with_wildcard',
                '$[*].bar[*]',
                '[{"bar":[42]}]',
                '[42]'
            ],
            [ // data set #80
                'bracket_notation_with_wildcard_after_recursive_descent',
                '$..[*]',
                '{"key":"value","another key":{"complex":"string","primitives":[0,1]}}',
                '["string","value",0,1,[0,1],{"complex":"string","primitives":[0,1]}]'
            ],
            [ // data set #81
                'bracket_notation_with_wildcard_on_array',
                '$[*]',
                '["string",42,{"key":"value"},[0,1]]',
                '["string",42,{"key":"value"},[0,1]]'
            ],
            [ // data set #82
                'bracket_notation_with_wildcard_on_empty_array',
                '$[*]',
                '[]',
                '[]'
            ],
            [ // data set #83
                'bracket_notation_with_wildcard_on_empty_object',
                '$[*]',
                '{}',
                '[]'
            ],
            [ // data set #84
                'bracket_notation_with_wildcard_on_null_value_array',
                '$[*]',
                '[40,null,42]',
                '[40,null,42]'
            ],
            [ // data set #85
                'bracket_notation_with_wildcard_on_object',
                '$[*]',
                '{"some":"string","int":42,"object":{"key":"value"},"array":[0,1]}',
                '["string",42,[0,1],{"key":"value"}]'
            ],
            [ // data set #86 - unknown consensus
                'bracket_notation_without_quotes',
                '$[key]',
                '{"key":"value"}',
                ''
            ],
            [ // data set #87 - unknown consensus
                'dot_bracket_notation',
                '$.[\'key\']',
                '{"key":"value","other":{"key":[{"key":42}]}}',
                ''
            ],
            [ // data set #88 - unknown consensus
                'dot_bracket_notation_with_double_quotes',
                '$.["key"]',
                '{"key":"value","other":{"key":[{"key":42}]}}',
                ''
            ],
            [ // data set #89 - unknown consensus
                'dot_bracket_notation_without_quotes',
                '$.[key]',
                '{"key":"value","other":{"key":[{"key":42}]}}',
                ''
            ],
            [ // data set #90
                'dot_notation',
                '$.key',
                '{"key":"value"}',
                '["value"]'
            ],
            [ // data set #91
                'dot_notation_after_array_slice',
                '$[0:2].key',
                '[{"key":"ey"},{"key":"bee"},{"key":"see"}]',
                '["ey","bee"]'
            ],
            [ // data set #92
                'dot_notation_after_bracket_notation_after_recursive_descent',
                '$..[1].key',
                '{"k":[{"key":"some value"},{"key":42}],"kk":[[{"key":100},{"key":200},{"key":300}],[{"key":400},{"k' .
                'ey":500},{"key":600}]],"key":[0,1]}',
                '[200,42,500]'
            ],
            [ // data set #93
                'dot_notation_after_bracket_notation_with_wildcard',
                '$[*].a',
                '[{"a":1},{"a":1}]',
                '[1,1]'
            ],
            [ // data set #94
                'dot_notation_after_bracket_notation_with_wildcard_on_one_matching',
                '$[*].a',
                '[{"a":1}]',
                '[1]'
            ],
            [ // data set #95
                'dot_notation_after_bracket_notation_with_wildcard_on_some_matching',
                '$[*].a',
                '[{"a":1},{"b":1}]',
                '[1]'
            ],
            [ // data set #96
                'dot_notation_after_filter_expression',
                '$[?(@.id==42)].name',
                '[{"id":42,"name":"forty-two"},{"id":1,"name":"one"}]',
                '["forty-two"]'
            ],
            [ // data set #97
                'dot_notation_after_recursive_descent',
                '$..key',
                '{"object":{"key":"value","array":[{"key":"something"},{"key":{"key":"russian dolls"}}]},"key":"top"' .
                '}',
                '["russian dolls","something","top","value",{"key":"russian dolls"}]'
            ],
            [ // data set #98
                'dot_notation_after_recursive_descent_after_dot_notation',
                '$.store..price',
                '{"store":{"book":[{"category":"reference","author":"Nigel Rees","title":"Sayings of the Century","p' .
                'rice":8.95},{"category":"fiction","author":"Evelyn Waugh","title":"Sword of Honour","price":12.99},' .
                '{"category":"fiction","author":"Herman Melville","title":"Moby Dick","isbn":"0-553-21311-3","price"' .
                ':8.99},{"category":"fiction","author":"J. R. R. Tolkien","title":"The Lord of the Rings","isbn":"0-' .
                '395-19395-8","price":22.99}],"bicycle":{"color":"red","price":19.95}}}',
                '[12.99,19.95,22.99,8.95,8.99]'
            ],
            [ // data set #99
                'dot_notation_after_union',
                '$[0,2].key',
                '[{"key":"ey"},{"key":"bee"},{"key":"see"}]',
                '["ey","see"]'
            ],
            [ // data set #100
                'dot_notation_after_union_with_keys',
                '$[\'one\',\'three\'].key',
                '{"one":{"key":"value"},"two":{"k":"v"},"three":{"some":"more","key":"other value"}}',
                '["value","other value"]'
            ],
            [ // data set #101
                'dot_notation_on_array',
                '$.key',
                '[0,1]',
                '[]'
            ],
            [ // data set #102
                'dot_notation_on_array_value',
                '$.key',
                '{"key":["first","second"]}',
                '[["first","second"]]'
            ],
            [ // data set #103
                'dot_notation_on_array_with_containing_object_matching_key',
                '$.id',
                '[{"id":2}]',
                '[]'
            ],
            [ // data set #104
                'dot_notation_on_empty_object_value',
                '$.key',
                '{"key":{}}',
                '[{}]'
            ],
            [ // data set #105
                'dot_notation_on_null_value',
                '$.key',
                '{"key":null}',
                '[null]'
            ],
            [ // data set #106
                'dot_notation_on_object_without_key',
                '$.missing',
                '{"key":"value"}',
                '[]'
            ],
            [ // data set #107
                'dot_notation_with_dash',
                '$.key-dash',
                '{"key-dash":"value"}',
                '["value"]'
            ],
            [ // data set #108 - unknown consensus
                'dot_notation_with_double_quotes',
                '$."key"',
                '{"key":"value","\\"key\\"":42}',
                ''
            ],
            [ // data set #109 - unknown consensus
                'dot_notation_with_double_quotes_after_recursive_descent',
                '$.."key"',
                '{"object":{"key":"value","\\"key\\"":100,"array":[{"key":"something","\\"key\\"":0},{"key":{"key":"' .
                'russian dolls"},"\\"key\\"":{"\\"key\\"":99}}]},"key":"top","\\"key\\"":42}',
                ''
            ],
            [ // data set #110 - unknown consensus
                'dot_notation_with_empty_path',
                '$.',
                '{"key":42,"":9001,"\'\'":"nice"}',
                ''
            ],
            [ // data set #111
                'dot_notation_with_key_named_in',
                '$.in',
                '{"in":"value"}',
                '["value"]'
            ],
            [ // data set #112
                'dot_notation_with_key_named_length',
                '$.length',
                '{"length":"value"}',
                '["value"]'
            ],
            [ // data set #113
                'dot_notation_with_key_named_length_on_array',
                '$.length',
                '[4,5,6]',
                '[3]'
            ],
            [ // data set #114
                'dot_notation_with_key_named_null',
                '$.null',
                '{"null":"value"}',
                '["value"]'
            ],
            [ // data set #115
                'dot_notation_with_key_named_true',
                '$.true',
                '{"true":"value"}',
                '["value"]'
            ],
            [ // data set #116 - unknown consensus, fallback to Proposal A
                'dot_notation_with_key_root_literal',
                '$.$',
                '{"$":"value"}',
                '["value"]'
            ],
            [ // data set #117
                'dot_notation_with_non_ASCII_key',
                '$.屬性',
                '{"\\u5c6c\\u6027":"value"}',
                '["value"]'
            ],
            [ // data set #118 - unknown consensus, fallback to Proposal A
                'dot_notation_with_number',
                '$.2',
                '["first","second","third","forth","fifth"]',
                '[]'
            ],
            [ // data set #119 - unknown consensus, fallback to Proposal A
                'dot_notation_with_number_-1',
                '$.-1',
                '["first","second","third","forth","fifth"]',
                '[]'
            ],
            [ // data set #120
                'dot_notation_with_number_on_object',
                '$.2',
                '{"a":"first","2":"second","b":"third"}',
                '["second"]'
            ],
            [ // data set #121 - unknown consensus
                'dot_notation_with_single_quotes',
                '$.\'key\'',
                '{"key":"value","\'key\'":42}',
                ''
            ],
            [ // data set #122 - unknown consensus
                'dot_notation_with_single_quotes_after_recursive_descent',
                '$..\'key\'',
                '{"object":{"key":"value","\'key\'":100,"array":[{"key":"something","\'key\'":0},{"key":{"key":"russ' .
                'ian dolls"},"\'key\'":{"\'key\'":99}}]},"key":"top","\'key\'":42}',
                ''
            ],
            [ // data set #123 - unknown consensus
                'dot_notation_with_single_quotes_and_dot',
                '$.\'some.key\'',
                '{"some.key":42,"some":{"key":"value"},"\'some.key\'":43}',
                ''
            ],
            [ // data set #124
                'dot_notation_with_wildcard_after_dot_notation_after_dot_notation_with_wildcard',
                '$.*.bar.*',
                '[{"bar":[42]}]',
                '[42]'
            ],
            [ // data set #125
                'dot_notation_with_wildcard_after_dot_notation_with_wildcard_on_nested_arrays',
                '$.*.*',
                '[[1,2,3],[4,5,6]]',
                '[1,2,3,4,5,6]'
            ],
            [ // data set #126
                'dot_notation_with_wildcard_after_recursive_descent',
                '$..*',
                '{"key":"value","another key":{"complex":"string","primitives":[0,1]}}',
                '["string","value",0,1,[0,1],{"complex":"string","primitives":[0,1]}]'
            ],
            [ // data set #127
                'dot_notation_with_wildcard_after_recursive_descent_on_null_value_array',
                '$..*',
                '[40,null,42]',
                '[40,42,null]'
            ],
            [ // data set #128
                'dot_notation_with_wildcard_after_recursive_descent_on_scalar',
                '$..*',
                '42',
                '[]'
            ],
            [ // data set #129
                'dot_notation_with_wildcard_on_array',
                '$.*',
                '["string",42,{"key":"value"},[0,1]]',
                '["string",42,{"key":"value"},[0,1]]'
            ],
            [ // data set #130
                'dot_notation_with_wildcard_on_empty_array',
                '$.*',
                '[]',
                '[]'
            ],
            [ // data set #131
                'dot_notation_with_wildcard_on_empty_object',
                '$.*',
                '{}',
                '[]'
            ],
            [ // data set #132
                'dot_notation_with_wildcard_on_object',
                '$.*',
                '{"some":"string","int":42,"object":{"key":"value"},"array":[0,1]}',
                '["string",42,[0,1],{"key":"value"}]'
            ],
            [ // data set #133 - unknown consensus
                'dot_notation_without_root',
                'key',
                '{"key":"value"}',
                ''
            ],
            [ // data set #134 - unknown consensus, fallback to Proposal A
                'filter_expression_after_dot_notation_with_wildcard_after_recursive_descent',
                '$..*[?(@.id>2)]',
                '[{"complext":{"one":[{"name":"first","id":1},{"name":"next","id":2},{"name":"another","id":3},{"nam' .
                'e":"more","id":4}],"more":{"name":"next to last","id":5}}},{"name":"last","id":6}]',
                '[{"id":3,"name":"another"},{"id":4,"name":"more"},{"id":5,"name":"next to last"}]'
            ],
            [ // data set #135 - unknown consensus, fallback to Proposal A
                'filter_expression_after_recursive_descent',
                '$..[?(@.id==2)]',
                '{"id":2,"more":[{"id":2},{"more":{"id":2}},{"id":{"id":2}},[{"id":2}]]}',
                '[{"id":2},{"id":2},{"id":2},{"id":2}]'
            ],
            [ // data set #136 - unknown consensus, fallback to Proposal A
                'filter_expression_on_object',
                '$[?(@.key)]',
                '{"key":42,"another":{"key":1}}',
                '[{"key":1}]'
            ],
            [ // data set #137 - unknown consensus
                'filter_expression_with_addition',
                '$[?(@.key+50==100)]',
                '[{"key":60},{"key":50},{"key":10},{"key":-50},{"key+50":100}]',
                ''
            ],
            [ // data set #138 - unknown consensus, fallback to Proposal A
                'filter_expression_with_boolean_and_operator',
                '$[?(@.key>42 && @.key<44)]',
                '[{"key":42},{"key":43},{"key":44}]',
                '[{"key":43}]'
            ],
            [ // data set #139 - unknown consensus
                'filter_expression_with_boolean_and_operator_and_value_false',
                '$[?(@.key>0 && false)]',
                '[{"key":1},{"key":3},{"key":"nice"},{"key":true},{"key":null},{"key":false},{"key":{}},{"key":[]},{' .
                '"key":-1},{"key":0},{"key":""}]',
                ''
            ],
            [ // data set #140 - unknown consensus
                'filter_expression_with_boolean_and_operator_and_value_true',
                '$[?(@.key>0 && true)]',
                '[{"key":1},{"key":3},{"key":"nice"},{"key":true},{"key":null},{"key":false},{"key":{}},{"key":[]},{' .
                '"key":-1},{"key":0},{"key":""}]',
                ''
            ],
            [ // data set #141 - unknown consensus, fallback to Proposal A
                'filter_expression_with_boolean_or_operator',
                '$[?(@.key>43 || @.key<43)]',
                '[{"key":42},{"key":43},{"key":44}]',
                '[{"key":42},{"key":44}]'
            ],
            [ // data set #142 - unknown consensus
                'filter_expression_with_boolean_or_operator_and_value_false',
                '$[?(@.key>0 || false)]',
                '[{"key":1},{"key":3},{"key":"nice"},{"key":true},{"key":null},{"key":false},{"key":{}},{"key":[]},{' .
                '"key":-1},{"key":0},{"key":""}]',
                ''
            ],
            [ // data set #143 - unknown consensus
                'filter_expression_with_boolean_or_operator_and_value_true',
                '$[?(@.key>0 || true)]',
                '[{"key":1},{"key":3},{"key":"nice"},{"key":true},{"key":null},{"key":false},{"key":{}},{"key":[]},{' .
                '"key":-1},{"key":0},{"key":""}]',
                ''
            ],
            [ // data set #144
                'filter_expression_with_bracket_notation',
                '$[?(@[\'key\']==42)]',
                '[{"key":0},{"key":42},{"key":-1},{"key":41},{"key":43},{"key":42.0001},{"key":41.9999},{"key":100},' .
                '{"some":"value"}]',
                '[{"key":42}]'
            ],
            [ // data set #145
                'filter_expression_with_bracket_notation_and_current_object_literal',
                '$[?(@[\'@key\']==42)]',
                '[{"@key":0},{"@key":42},{"key":42},{"@key":43},{"some":"value"}]',
                '[{"@key":42}]'
            ],
            [ // data set #146 - unknown consensus, fallback to Proposal A
                'filter_expression_with_bracket_notation_with_-1',
                '$[?(@[-1]==2)]',
                '[[2,3],["a"],[0,2],[2]]',
                '[[0,2],[2]]'
            ],
            [ // data set #147
                'filter_expression_with_bracket_notation_with_number',
                '$[?(@[1]==\'b\')]',
                '[["a","b"],["x","y"]]',
                '[["a","b"]]'
            ],
            [ // data set #148 - unknown consensus, fallback to Proposal A
                'filter_expression_with_bracket_notation_with_number_on_object',
                '$[?(@[1]==\'b\')]',
                '{"1":["a","b"],"2":["x","y"]}',
                '[["a","b"]]'
            ],
            [ // data set #149 - unknown consensus, fallback to Proposal A
                'filter_expression_with_current_object',
                '$[?(@)]',
                '["some value",null,"value",0,1,-1,"",[],{},false,true]',
                '["some value",null,"value",0,1,-1,"",[],{},false,true]'
            ],
            [ // data set #150 - unknown consensus, fallback to Proposal A
                'filter_expression_with_different_grouped_operators',
                '$[?(@.a && (@.b || @.c))]',
                '[{"a":true},{"a":true,"b":true},{"a":true,"b":true,"c":true},{"b":true,"c":true},{"a":true,"c":true' .
                '},{"c":true},{"b":true}]',
                '[{"a":true,"b":true},{"a":true,"b":true,"c":true},{"a":true,"c":true}]'
            ],
            [ // data set #151 - unknown consensus
                'filter_expression_with_different_ungrouped_operators',
                '$[?(@.a && @.b || @.c)]',
                '[{"a":true,"b":true},{"a":true,"b":true,"c":true},{"b":true,"c":true},{"a":true,"c":true},{"a":true' .
                '},{"b":true},{"c":true},{"d":true},{}]',
                ''
            ],
            [ // data set #152 - unknown consensus
                'filter_expression_with_division',
                '$[?(@.key/10==5)]',
                '[{"key":60},{"key":50},{"key":10},{"key":-50},{"key\\/10":5}]',
                ''
            ],
            [ // data set #153 - unknown consensus
                'filter_expression_with_empty_expression',
                '$[?()]',
                '[1,{"key":42},"value",null]',
                ''
            ],
            [ // data set #154 - unknown consensus, fallback to Proposal A
                'filter_expression_with_equals',
                '$[?(@.key==42)]',
                '[{"key":0},{"key":42},{"key":-1},{"key":1},{"key":41},{"key":43},{"key":42.0001},{"key":41.9999},{"' .
                'key":100},{"key":"some"},{"key":"42"},{"key":null},{"key":420},{"key":""},{"key":{}},{"key":[]},{"k' .
                'ey":[42]},{"key":{"key":42}},{"key":{"some":42}},{"some":"value"}]',
                '[{"key":42}]'
            ],
            [ // data set #155 - unknown consensus
                'filter_expression_with_equals_array',
                '$[?(@.d==["v1","v2"])]',
                '[{"d":["v1","v2"]},{"d":["a","b"]},{"d":"v1"},{"d":"v2"},{"d":{}},{"d":[]},{"d":null},{"d":-1},{"d"' .
                ':0},{"d":1},{"d":"[\'v1\',\'v2\']"},{"d":"[\'v1\', \'v2\']"},{"d":"v1,v2"},{"d":"[\\"v1\\", \\"v2\\' .
                '"]"},{"d":"[\\"v1\\",\\"v2\\"]"}]',
                ''
            ],
            [ // data set #156 - unknown consensus
                'filter_expression_with_equals_array_for_array_slice_with_range_1',
                '$[?(@[0:1]==[1])]',
                '[[1,2,3],[1],[2,3],1,2]',
                ''
            ],
            [ // data set #157 - unknown consensus
                'filter_expression_with_equals_array_for_dot_notation_with_star',
                '$[?(@.*==[1,2])]',
                '[[1,2],[2,3],[1],[2],[1,2,3],1,2,3]',
                ''
            ],
            [ // data set #158 - unknown consensus
                'filter_expression_with_equals_array_with_single_quotes',
                '$[?(@.d==[\'v1\',\'v2\'])]',
                '[{"d":["v1","v2"]},{"d":["a","b"]},{"d":"v1"},{"d":"v2"},{"d":{}},{"d":[]},{"d":null},{"d":-1},{"d"' .
                ':0},{"d":1},{"d":"[\'v1\',\'v2\']"},{"d":"[\'v1\', \'v2\']"},{"d":"v1,v2"},{"d":"[\\"v1\\", \\"v2\\' .
                '"]"},{"d":"[\\"v1\\",\\"v2\\"]"}]',
                ''
            ],
            [ // data set #159 - unknown consensus
                'filter_expression_with_equals_boolean_expression_value',
                '$[?((@.key<44)==false)]',
                '[{"key":42},{"key":43},{"key":44}]',
                ''
            ],
            [ // data set #160 - unknown consensus, fallback to Proposal A
                'filter_expression_with_equals_false',
                '$[?(@.key==false)]',
                '[{"some":"some value"},{"key":true},{"key":false},{"key":null},{"key":"value"},{"key":""},{"key":0}' .
                ',{"key":1},{"key":-1},{"key":42},{"key":{}},{"key":[]}]',
                '[{"key":false}]'
            ],
            [ // data set #161 - unknown consensus, fallback to Proposal A
                'filter_expression_with_equals_null',
                '$[?(@.key==null)]',
                '[{"some":"some value"},{"key":true},{"key":false},{"key":null},{"key":"value"},{"key":""},{"key":0}' .
                ',{"key":1},{"key":-1},{"key":42},{"key":{}},{"key":[]}]',
                '[{"key":null}]'
            ],
            [ // data set #162 - unknown consensus
                'filter_expression_with_equals_number_for_array_slice_with_range_1',
                '$[?(@[0:1]==1)]',
                '[[1,2,3],[1],[2,3],1,2]',
                ''
            ],
            [ // data set #163 - unknown consensus
                'filter_expression_with_equals_number_for_bracket_notation_with_star',
                '$[?(@[*]==2)]',
                '[[1,2],[2,3],[1],[2],[1,2,3],1,2,3]',
                ''
            ],
            [ // data set #164 - unknown consensus
                'filter_expression_with_equals_number_for_dot_notation_with_star',
                '$[?(@.*==2)]',
                '[[1,2],[2,3],[1],[2],[1,2,3],1,2,3]',
                ''
            ],
            [ // data set #165 - unknown consensus, fallback to Proposal A
                'filter_expression_with_equals_number_with_fraction',
                '$[?(@.key==-0.123e2)]',
                '[{"key":-12.3},{"key":-0.123},{"key":-12},{"key":12.3},{"key":2},{"key":"-0.123e2"}]',
                '[{"key":-12.3}]'
            ],
            [ // data set #166 - unknown consensus
                'filter_expression_with_equals_number_with_leading_zeros',
                '$[?(@.key==010)]',
                '[{"key":"010"},{"key":"10"},{"key":10},{"key":0},{"key":8}]',
                ''
            ],
            [ // data set #167 - unknown consensus
                'filter_expression_with_equals_object',
                '$[?(@.d=={"k":"v"})]',
                '[{"d":{"k":"v"}},{"d":{"a":"b"}},{"d":"k"},{"d":"v"},{"d":{}},{"d":[]},{"d":null},{"d":-1},{"d":0},' .
                '{"d":1},{"d":"[object Object]"},{"d":"{\\"k\\": \\"v\\"}"},{"d":"{\\"k\\":\\"v\\"}"},"v"]',
                ''
            ],
            [ // data set #168 - unknown consensus, fallback to Proposal A
                'filter_expression_with_equals_on_array_of_numbers',
                '$[?(@==42)]',
                '[0,42,-1,41,43,42.0001,41.9999,null,100]',
                '[42]'
            ],
            [ // data set #169
                'filter_expression_with_equals_on_array_without_match',
                '$[?(@.key==43)]',
                '[{"key":42}]',
                '[]'
            ],
            [ // data set #170 - unknown consensus, fallback to Proposal A
                'filter_expression_with_equals_on_object',
                '$[?(@.key==42)]',
                '{"a":{"key":0},"b":{"key":42},"c":{"key":-1},"d":{"key":41},"e":{"key":43},"f":{"key":42.0001},"g":' .
                '{"key":41.9999},"h":{"key":100},"i":{"some":"value"}}',
                '[{"key":42}]'
            ],
            [ // data set #171 - unknown consensus, fallback to Proposal A
                'filter_expression_with_equals_on_object_with_key_matching_query',
                '$[?(@.id==2)]',
                '{"id":2}',
                '[]'
            ],
            [ // data set #172 - unknown consensus, fallback to Proposal A
                'filter_expression_with_equals_string',
                '$[?(@.key=="value")]',
                '[{"key":"some"},{"key":"value"},{"key":null},{"key":0},{"key":1},{"key":-1},{"key":""},{"key":{}},{' .
                '"key":[]},{"key":"valuemore"},{"key":"morevalue"},{"key":["value"]},{"key":{"some":"value"}},{"key"' .
                ':{"key":"value"}},{"some":"value"}]',
                '[{"key":"value"}]'
            ],
            [ // data set #173
                'filter_expression_with_equals_string_with_current_object_literal',
                '$[?(@.key=="hi@example.com")]',
                '[{"key":"some"},{"key":"value"},{"key":"hi@example.com"}]',
                '[{"key":"hi@example.com"}]'
            ],
            [ // data set #174
                'filter_expression_with_equals_string_with_dot_literal',
                '$[?(@.key=="some.value")]',
                '[{"key":"some"},{"key":"value"},{"key":"some.value"}]',
                '[{"key":"some.value"}]'
            ],
            [ // data set #175
                'filter_expression_with_equals_string_with_single_quotes',
                '$[?(@.key==\'value\')]',
                '[{"key":"some"},{"key":"value"}]',
                '[{"key":"value"}]'
            ],
            [ // data set #176 - unknown consensus, fallback to Proposal A
                'filter_expression_with_equals_true',
                '$[?(@.key==true)]',
                '[{"some":"some value"},{"key":true},{"key":false},{"key":null},{"key":"value"},{"key":""},{"key":0}' .
                ',{"key":1},{"key":-1},{"key":42},{"key":{}},{"key":[]}]',
                '[{"key":true}]'
            ],
            [ // data set #177 - unknown consensus, fallback to Proposal A
                'filter_expression_with_equals_with_root_reference',
                '$.items[?(@.key==$.value)]',
                '{"value":42,"items":[{"key":10},{"key":42},{"key":50}]}',
                '[{"key":42}]'
            ],
            [ // data set #178 - unknown consensus, fallback to Proposal A
                'filter_expression_with_greater_than',
                '$[?(@.key>42)]',
                '[{"key":0},{"key":42},{"key":-1},{"key":41},{"key":43},{"key":42.0001},{"key":41.9999},{"key":100},' .
                '{"key":"43"},{"key":"42"},{"key":"41"},{"key":"value"},{"some":"value"}]',
                '[{"key":43},{"key":42.0001},{"key":100}]'
            ],
            [ // data set #179 - unknown consensus, fallback to Proposal A
                'filter_expression_with_greater_than_or_equal',
                '$[?(@.key>=42)]',
                '[{"key":0},{"key":42},{"key":-1},{"key":41},{"key":43},{"key":42.0001},{"key":41.9999},{"key":100},' .
                '{"key":"43"},{"key":"42"},{"key":"41"},{"key":"value"},{"some":"value"}]',
                '[{"key":42},{"key":43},{"key":42.0001},{"key":100}]'
            ],
            [ // data set #180 - unknown consensus
                'filter_expression_with_in_array_of_values',
                '$[?(@.d in [2, 3])]',
                '[{"d":1},{"d":2},{"d":1},{"d":3},{"d":4}]',
                '[{"d":2},{"d":3}]'
            ],
            [ // data set #181 - unknown consensus
                'filter_expression_with_in_current_object',
                '$[?(2 in @.d)]',
                '[{"d":[1,2,3]},{"d":[2]},{"d":[1]},{"d":[3,4]},{"d":[4,2]}]',
                ''
            ],
            [ // data set #182 - unknown consensus, fallback to Proposal A
                'filter_expression_with_less_than',
                '$[?(@.key<42)]',
                '[{"key":0},{"key":42},{"key":-1},{"key":41},{"key":43},{"key":42.0001},{"key":41.9999},{"key":100},' .
                '{"key":"43"},{"key":"42"},{"key":"41"},{"key":"value"},{"some":"value"}]',
                '[{"key":0},{"key":-1},{"key":41},{"key":41.9999}]'
            ],
            [ // data set #183 - unknown consensus, fallback to Proposal A
                'filter_expression_with_less_than_or_equal',
                '$[?(@.key<=42)]',
                '[{"key":0},{"key":42},{"key":-1},{"key":41},{"key":43},{"key":42.0001},{"key":41.9999},{"key":100},' .
                '{"key":"43"},{"key":"42"},{"key":"41"},{"key":"value"},{"some":"value"}]',
                '[{"key":0},{"key":42},{"key":-1},{"key":41},{"key":41.9999}]'
            ],
            [ // data set #184 - unknown consensus
                'filter_expression_with_multiplication',
                '$[?(@.key*2==100)]',
                '[{"key":60},{"key":50},{"key":10},{"key":-50},{"key*2":100}]',
                ''
            ],
            [ // data set #185 - unknown consensus, fallback to Proposal A
                'filter_expression_with_negation_and_equals',
                '$[?(!(@.key==42))]',
                '[{"key":0},{"key":42},{"key":-1},{"key":41},{"key":43},{"key":42.0001},{"key":41.9999},{"key":100},' .
                '{"key":"43"},{"key":"42"},{"key":"41"},{"key":"value"},{"some":"value"}]',
                '[{"key":0},{"key":-1},{"key":41},{"key":43},{"key":42.0001},{"key":41.9999},{"key":100},{"key":"43"' .
                '},{"key":"42"},{"key":"41"},{"key":"value"},{"some":"value"}]'
            ],
            [ // data set #186 - unknown consensus, fallback to Proposal A
                'filter_expression_with_negation_and_less_than',
                '$[?(!(@.key<42))]',
                '[{"key":0},{"key":42},{"key":-1},{"key":41},{"key":43},{"key":42.0001},{"key":41.9999},{"key":100},' .
                '{"key":"43"},{"key":"42"},{"key":"41"},{"key":"value"},{"some":"value"}]',
                '[{"key":42},{"key":43},{"key":42.0001},{"key":100},{"key":"43"},{"key":"42"},{"key":"41"},{"key":"v' .
                'alue"},{"some":"value"}]'
            ],
            [ // data set #187 - unknown consensus, fallback to Proposal A
                'filter_expression_with_not_equals',
                '$[?(@.key!=42)]',
                '[{"key":0},{"key":42},{"key":-1},{"key":1},{"key":41},{"key":43},{"key":42.0001},{"key":41.9999},{"' .
                'key":100},{"key":"some"},{"key":"42"},{"key":null},{"key":420},{"key":""},{"key":{}},{"key":[]},{"k' .
                'ey":[42]},{"key":{"key":42}},{"key":{"some":42}},{"some":"value"}]',
                '[{"key":0},{"key":-1},{"key":1},{"key":41},{"key":43},{"key":42.0001},{"key":41.9999},{"key":100},{' .
                '"key":"some"},{"key":"42"},{"key":null},{"key":420},{"key":""},{"key":{}},{"key":[]},{"key":[42]},{' .
                '"key":{"key":42}},{"key":{"some":42}},{"some":"value"}]'
            ],
            [ // data set #188 - unknown consensus
                'filter_expression_with_regular_expression',
                '$[?(@.name=~/hello.*/)]',
                '[{"name":"hullo world"},{"name":"hello world"},{"name":"yes hello world"},{"name":"HELLO WORLD"},{"' .
                'name":"good bye"}]',
                ''
            ],
            [ // data set #189 - unknown consensus
                'filter_expression_with_set_wise_comparison_to_scalar',
                '$[?(@[*]>=4)]',
                '[[1,2],[3,4],[5,6]]',
                ''
            ],
            [ // data set #190 - unknown consensus
                'filter_expression_with_set_wise_comparison_to_set',
                '$.x[?(@[*]>=$.y[*])]',
                '{"x":[[1,2],[3,4],[5,6]],"y":[3,4,5]}',
                ''
            ],
            [ // data set #191 - unknown consensus
                'filter_expression_with_single_equal',
                '$[?(@.key=42)]',
                '[{"key":0},{"key":42},{"key":-1},{"key":1},{"key":41},{"key":43},{"key":42.0001},{"key":41.9999},{"' .
                'key":100},{"key":"some"},{"key":"42"},{"key":null},{"key":420},{"key":""},{"key":{}},{"key":[]},{"k' .
                'ey":[42]},{"key":{"key":42}},{"key":{"some":42}},{"some":"value"}]',
                ''
            ],
            [ // data set #192 - unknown consensus
                'filter_expression_with_subfilter',
                '$[?(@.a[?(@.price>10)])]',
                '[{"a":[{"price":1},{"price":3}]},{"a":[{"price":11}]},{"a":[{"price":8},{"price":12},{"price":3}]},' .
                '{"a":[]}]',
                ''
            ],
            [ // data set #193
                'filter_expression_with_subpaths',
                '$[?(@.address.city==\'Berlin\')]',
                '[{"address":{"city":"Berlin"}},{"address":{"city":"London"}}]',
                '[{"address":{"city":"Berlin"}}]'
            ],
            [ // data set #194 - unknown consensus, fallback to Proposal A
                'filter_expression_with_subtraction',
                '$[?(@.key-50==-100)]',
                '[{"key":60},{"key":50},{"key":10},{"key":-50},{"key-50":-100}]',
                '[{"key-50":-100}]'
            ],
            [ // data set #195 - unknown consensus, fallback to Proposal A
                'filter_expression_with_tautological_comparison',
                '$[?(1==1)]',
                '[1,3,"nice",true,null,false,{},[],-1,0,""]',
                '[1,3,"nice",true,null,false,{},[],-1,0,""]'
            ],
            [ // data set #196 - unknown consensus
                'filter_expression_with_triple_equal',
                '$[?(@.key===42)]',
                '[{"key":0},{"key":42},{"key":-1},{"key":1},{"key":41},{"key":43},{"key":42.0001},{"key":41.9999},{"' .
                'key":100},{"key":"some"},{"key":"42"},{"key":null},{"key":420},{"key":""},{"key":{}},{"key":[]},{"k' .
                'ey":[42]},{"key":{"key":42}},{"key":{"some":42}},{"some":"value"}]',
                ''
            ],
            [ // data set #197 - unknown consensus, fallback to Proposal A
                'filter_expression_with_value',
                '$[?(@.key)]',
                '[{"some":"some value"},{"key":true},{"key":false},{"key":null},{"key":"value"},{"key":""},{"key":0}' .
                ',{"key":1},{"key":-1},{"key":42},{"key":{}},{"key":[]}]',
                '[{"key":true},{"key":false},{"key":null},{"key":"value"},{"key":""},{"key":0},{"key":1},{"key":-1},' .
                '{"key":42},{"key":{}},{"key":[]}]'
            ],
            [ // data set #198 - unknown consensus, fallback to Proposal A
                'filter_expression_with_value_after_dot_notation_with_wildcard_on_array_of_objects',
                '$.*[?(@.key)]',
                '[{"some":"some value"},{"key":"value"}]',
                '[]'
            ],
            [ // data set #199 - unknown consensus, fallback to Proposal A
                'filter_expression_with_value_after_recursive_descent',
                '$..[?(@.id)]',
                '{"id":2,"more":[{"id":2},{"more":{"id":2}},{"id":{"id":2}},[{"id":2}]]}',
                '[{"id":2},{"id":2},{"id":2},{"id":2},{"id":{"id":2}}]'
            ],
            [ // data set #200 - unknown consensus
                'filter_expression_with_value_false',
                '$[?(false)]',
                '[1,3,"nice",true,null,false,{},[],-1,0,""]',
                ''
            ],
            [ // data set #201 - unknown consensus
                'filter_expression_with_value_from_recursive_descent',
                '$[?(@..child)]',
                '[{"key":[{"child":1},{"child":2}]},{"key":[{"child":2}]},{"key":[{}]},{"key":[{"something":42}]},{}' .
                ']',
                ''
            ],
            [ // data set #202 - unknown consensus
                'filter_expression_with_value_null',
                '$[?(null)]',
                '[1,3,"nice",true,null,false,{},[],-1,0,""]',
                ''
            ],
            [ // data set #203 - unknown consensus
                'filter_expression_with_value_true',
                '$[?(true)]',
                '[1,3,"nice",true,null,false,{},[],-1,0,""]',
                ''
            ],
            [ // data set #204 - unknown consensus
                'filter_expression_without_parens',
                '$[?@.key==42]',
                '[{"key":0},{"key":42},{"key":-1},{"key":1},{"key":41},{"key":43},{"key":42.0001},{"key":41.9999},{"' .
                'key":100},{"key":"some"},{"key":"42"},{"key":null},{"key":420},{"key":""},{"key":{}},{"key":[]},{"k' .
                'ey":[42]},{"key":{"key":42}},{"key":{"some":42}},{"some":"value"}]',
                ''
            ],
            [ // data set #205 - unknown consensus, fallback to Proposal A
                'filter_expression_without_value',
                '$[?(!@.key)]',
                '[{"some":"some value"},{"key":true},{"key":false},{"key":null},{"key":"value"},{"key":""},{"key":0}' .
                ',{"key":1},{"key":-1},{"key":42},{"key":{}},{"key":[]}]',
                '[{"some":"some value"}]'
            ],
            [ // data set #206 - unknown consensus
                'parens_notation',
                '$(key,more)',
                '{"key":1,"some":2,"more":3}',
                ''
            ],
            [ // data set #207 - unknown consensus
                'recursive_descent',
                '$..',
                '[{"a":{"b":"c"}},[0,1]]',
                ''
            ],
            [ // data set #208 - unknown consensus
                'recursive_descent_after_dot_notation',
                '$.key..',
                '{"some key":"value","key":{"complex":"string","primitives":[0,1]}}',
                ''
            ],
            [ // data set #209
                'root',
                '$',
                '{"key":"value","another key":{"complex":["a",1]}}',
                '[{"another key":{"complex":["a",1]},"key":"value"}]'
            ],
            [ // data set #210
                'root_on_scalar',
                '$',
                '42',
                '[42]'
            ],
            [ // data set #211
                'root_on_scalar_false',
                '$',
                'false',
                '[false]'
            ],
            [ // data set #212
                'root_on_scalar_true',
                '$',
                'true',
                '[true]'
            ],
            [ // data set #213 - unknown consensus
                'script_expression',
                '$[(@.length-1)]',
                '["first","second","third","forth","fifth"]',
                'fifth'
            ],
            [ // data set #214
                'union',
                '$[0,1]',
                '["first","second","third"]',
                '["first","second"]'
            ],
            [ // data set #215 - unknown consensus, fallback to Proposal A
                'union_with_filter',
                '$[?(@.key<3),?(@.key>6)]',
                '[{"key":1},{"key":8},{"key":3},{"key":10},{"key":7},{"key":2},{"key":6},{"key":4}]',
                '[{"key":1},{"key":2},{"key":8},{"key":10},{"key":7}]'
            ],
            [ // data set #216
                'union_with_keys',
                '$[\'key\',\'another\']',
                '{"key":"value","another":"entry"}',
                '["value","entry"]'
            ],
            [ // data set #217
                'union_with_keys_after_array_slice',
                '$[:][\'c\',\'d\']',
                '[{"c":"cc1","d":"dd1","e":"ee1"},{"c":"cc2","d":"dd2","e":"ee2"}]',
                '["cc1","dd1","cc2","dd2"]'
            ],
            [ // data set #218
                'union_with_keys_after_bracket_notation',
                '$[0][\'c\',\'d\']',
                '[{"c":"cc1","d":"dd1","e":"ee1"},{"c":"cc2","d":"dd2","e":"ee2"}]',
                '["cc1","dd1"]'
            ],
            [ // data set #219 - unknown consensus, fallback to Proposal A
                'union_with_keys_after_dot_notation_with_wildcard',
                '$.*[\'c\',\'d\']',
                '[{"c":"cc1","d":"dd1","e":"ee1"},{"c":"cc2","d":"dd2","e":"ee2"}]',
                '["cc1","dd1","cc2","dd2"]'
            ],
            [ // data set #220 - unknown consensus, fallback to Proposal A
                'union_with_keys_after_recursive_descent',
                '$..[\'c\',\'d\']',
                '[{"c":"cc1","d":"dd1","e":"ee1"},{"c":"cc2","child":{"d":"dd2"}},{"c":"cc3"},{"d":"dd4"},{"child":{' .
                '"c":"cc5"}}]',
                '["cc1","cc2","cc3","cc5","dd1","dd2","dd4"]'
            ],
            [ // data set #221
                'union_with_keys_on_object_without_key',
                '$[\'missing\',\'key\']',
                '{"key":"value","another":"entry"}',
                '["value"]'
            ],
            [ // data set #222
                'union_with_numbers_in_decreasing_order',
                '$[4,1]',
                '[1,2,3,4,5]',
                '[5,2]'
            ],
            [ // data set #223 - unknown consensus, fallback to Proposal A
                'union_with_repeated_matches_after_dot_notation_with_wildcard',
                '$.*[0,:5]',
                '{"a":["string",null,true],"b":[false,"string",5.4]}',
                '["string","string",null,true,false,false,"string",5.4]'
            ],
            [ // data set #224 - unknown consensus, fallback to Proposal A
                'union_with_slice_and_number',
                '$[1:3,4]',
                '[1,2,3,4,5]',
                '[2,3,5]'
            ],
            [ // data set #225
                'union_with_spaces',
                '$[ 0 , 1 ]',
                '["first","second","third"]',
                '["first","second"]'
            ],
            [ // data set #226 - unknown consensus, fallback to Proposal A
                'union_with_wildcard_and_number',
                '$[*,1]',
                '["first","second","third","forth","fifth"]',
                '["first","second","third","forth","fifth","second"]'
            ],
        ];
    }
}
