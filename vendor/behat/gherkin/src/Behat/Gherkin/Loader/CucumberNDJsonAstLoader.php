<?php

namespace Behat\Gherkin\Loader;

use Behat\Gherkin\Node\BackgroundNode;
use Behat\Gherkin\Node\ExampleTableNode;
use Behat\Gherkin\Node\FeatureNode;
use Behat\Gherkin\Node\OutlineNode;
use Behat\Gherkin\Node\ScenarioInterface;
use Behat\Gherkin\Node\ScenarioNode;
use Behat\Gherkin\Node\StepNode;

/**
 * Loads a feature from cucumber's protobuf JSON format
 */
class CucumberNDJsonAstLoader implements LoaderInterface
{

    public function supports($resource)
    {
        return is_string($resource);
    }

    public function load($resource)
    {
        return array_values(array_filter(array_map(
            static function ($line) use ($resource) {
                return self::getFeature(json_decode($line, true), $resource);
            },
            file($resource)
        )));
    }

    /**
     * @return FeatureNode|null
     */
    private static function getFeature(array $json, $filePath)
    {
        if (!isset($json['gherkinDocument']['feature'])) {
            return null;
        }

        $featureJson = $json['gherkinDocument']['feature'];

        $feature = new FeatureNode(
            isset($featureJson['name']) ? $featureJson['name'] : null,
            isset($featureJson['description']) ? trim($featureJson['description']) : null,
            self::getTags($featureJson),
            self::getBackground($featureJson),
            self::getScenarios($featureJson),
            $featureJson['keyword'],
            $featureJson['language'],
            preg_replace('/(?<=\\.feature).*$/', '', $filePath),
            $featureJson['location']['line']
        );

        return $feature;
    }

    /**
     * @return string[]
     */
    private static function getTags(array $json)
    {
        return array_map(
            static function(array $tag) { return preg_replace('/^@/', '', $tag['name']); },
            isset($json['tags']) ? $json['tags'] : []
        );
    }

    /**
     * @return ScenarioInterface[]
     */
    private static function getScenarios(array $json)
    {

        return array_values(
            array_map(
                static function ($child) {

                    if (isset($child['scenario']['examples'])) {
                        return new OutlineNode(
                            isset($child['scenario']['name']) ? $child['scenario']['name'] : null,
                            self::getTags($child['scenario']),
                            self::getSteps(isset($child['scenario']['steps']) ? $child['scenario']['steps'] : []),
                            self::getTables($child['scenario']['examples']),
                            $child['scenario']['keyword'],
                            $child['scenario']['location']['line']
                        );
                    }
                    else {
                        return new ScenarioNode(
                            $child['scenario']['name'],
                            self::getTags($child['scenario']),
                            self::getSteps(isset($child['scenario']['steps']) ? $child['scenario']['steps'] : []),
                            $child['scenario']['keyword'],
                            $child['scenario']['location']['line']
                        );
                    }

                },
                array_filter(
                    isset($json['children']) ? $json['children'] : [],
                    static function ($child) {
                        return isset($child['scenario']);
                    }
                )
            )
        );
    }

    /**
     * @return BackgroundNode|null
     */
    private static function getBackground(array $json)
    {
        $backgrounds = array_values(
            array_map(
                static function ($child) {
                    return new BackgroundNode(
                        $child['background']['name'],
                        self::getSteps(isset($child['background']['steps']) ? $child['background']['steps'] : []),
                        $child['background']['keyword'],
                        $child['background']['location']['line']
                    );
                },
                array_filter(
                    isset($json['children']) ? $json['children'] : [],
                    static function ($child) {
                        return isset($child['background']);
                    }
                )
            )
        );

        return count($backgrounds) == 1 ? $backgrounds[0] : null;
    }

    /**
     * @return StepNode[]
     */
    private static function getSteps(array $json)
    {
        return array_map(
            static function(array $json) {
                return new StepNode(
                    trim($json['keyword']),
                    $json['text'],
                    [],
                    $json['location']['line'],
                    trim($json['keyword'])
                );
            },
            $json
        );
    }

    /**
     * @return ExampleTableNode[]
     */
    private static function getTables(array $json)
    {
        return array_map(
            static function($tableJson) {

                $table = [];

                $table[$tableJson['tableHeader']['location']['line']] = array_map(
                    static function($cell) {
                        return $cell['value'];
                    },
                    $tableJson['tableHeader']['cells']
                );

                foreach ($tableJson['tableBody'] as $bodyRow) {
                    $table[$bodyRow['location']['line']] = array_map(
                        static function($cell) {
                            return $cell['value'];
                        },
                        $bodyRow['cells']
                    );
                }

                return new ExampleTableNode(
                    $table,
                    $tableJson['keyword'],
                    self::getTags($tableJson)
                );
            },
            $json
        );
    }
}
