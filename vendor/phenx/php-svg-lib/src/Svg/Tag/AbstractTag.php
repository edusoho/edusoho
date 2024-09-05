<?php
/**
 * @package php-svg-lib
 * @link    http://github.com/PhenX/php-svg-lib
 * @author  Fabien Ménager <fabien.menager@gmail.com>
 * @license GNU LGPLv3+ http://www.gnu.org/copyleft/lesser.html
 */

namespace Svg\Tag;

use Svg\CssLength;
use Svg\Document;
use Svg\Style;

abstract class AbstractTag
{
    /** @var Document */
    protected $document;

    public $tagName;

    /** @var Style */
    protected $style;

    protected $attributes = array();

    protected $hasShape = true;

    /** @var self[] */
    protected $children = array();

    public function __construct(Document $document, $tagName)
    {
        $this->document = $document;
        $this->tagName = $tagName;
    }

    public function getDocument(){
        return $this->document;
    }

    /**
     * @return Group|null
     */
    public function getParentGroup() {
        $stack = $this->getDocument()->getStack();
        for ($i = count($stack)-2; $i >= 0; $i--) {
            $tag = $stack[$i];

            if ($tag instanceof Group || $tag instanceof Document) {
                return $tag;
            }
        }

        return null;
    }

    public function handle($attributes)
    {
        $this->attributes = $attributes;

        if (!$this->getDocument()->inDefs || $this instanceof StyleTag) {
            $this->before($attributes);
            $this->start($attributes);
        }
    }

    public function handleEnd()
    {
        if (!$this->getDocument()->inDefs || $this instanceof StyleTag) {
            $this->end();
            $this->after();
        }
    }

    protected function before($attributes)
    {
    }

    protected function start($attributes)
    {
    }

    protected function end()
    {
    }

    protected function after()
    {
    }

    public function getAttributes()
    {
        return $this->attributes;
    }

    protected function setStyle(Style $style)
    {
        $this->style = $style;

        if ($style->display === "none") {
            $this->hasShape = false;
        }
    }

    /**
     * @return Style
     */
    public function getStyle()
    {
        return $this->style;
    }

    /**
     * Make a style object from the tag and its attributes
     *
     * @param array $attributes
     *
     * @return Style
     */
    protected function makeStyle($attributes) {
        $style = new Style($this->document);
        $style->inherit($this);
        $style->fromStyleSheets($this, $attributes);
        $style->fromAttributes($attributes);

        return $style;
    }

    protected function applyTransform($attributes)
    {

        if (isset($attributes["transform"])) {
            $surface = $this->document->getSurface();

            $transform = $attributes["transform"];

            $matches = array();
            preg_match_all(
                '/(matrix|translate|scale|rotate|skew|skewX|skewY)\((.*?)\)/is',
                $transform,
                $matches,
                PREG_SET_ORDER
            );

            $transformations = array();
            foreach ($matches as $match) {
                $arguments = preg_split('/[ ,]+/', $match[2]);
                array_unshift($arguments, $match[1]);
                $transformations[] = $arguments;
            }

            foreach ($transformations as $t) {
                switch ($t[0]) {
                    case "matrix":
                        $surface->transform($t[1], $t[2], $t[3], $t[4], $t[5], $t[6]);
                        break;

                    case "translate":
                        $surface->translate($t[1], isset($t[2]) ? $t[2] : 0);
                        break;

                    case "scale":
                        $surface->scale($t[1], isset($t[2]) ? $t[2] : $t[1]);
                        break;

                    case "rotate":
                        if (isset($t[2])) {
                            $t[3] = isset($t[3]) ? $t[3] : 0;
                            $surface->translate($t[2], $t[3]);
                            $surface->rotate($t[1]);
                            $surface->translate(-$t[2], -$t[3]);
                        } else {
                            $surface->rotate($t[1]);
                        }
                        break;

                    case "skewX":
                        $tan_x = tan(deg2rad($t[1]));
                        $surface->transform(1, 0, $tan_x, 1, 0, 0);
                        break;

                    case "skewY":
                        $tan_y = tan(deg2rad($t[1]));
                        $surface->transform(1, $tan_y, 0, 1, 0, 0);
                        break;
                }
            }
        }
    }

    /**
     * Apply a viewBox transform to the element
     *
     * @param array $attributes
     */
    protected function applyViewbox($attributes) {
        if (!isset($attributes["viewbox"])) {
            return;
        }

        $surface = $this->document->getSurface();
        $viewBox = preg_split('/[\s,]+/is', trim($attributes['viewbox']));
        if (count($viewBox) != 4) {
            return;
        }

        // Computing the equivalent transform of an SVG viewport
        // https://svgwg.org/svg2-draft/coords.html#ComputingAViewportsTransform

        // 1. Let vb-x, vb-y, vb-width, vb-height be the min-x, min-y, width and height values of the viewBox attribute respectively.
        [$vbX, $vbY, $vbWidth, $vbHeight] = $viewBox;

        if ($vbWidth < 0 || $vbHeight < 0) {
            return;
        }

        // correct solution is to not render, for now scaling to 0 below
        //if ($vbWidth == 0 || $vbHeight == 0) {
        //}

        // 2. Let e-x, e-y, e-width, e-height be the position and size of the element respectively.
        $eX = $attributes["x"] ?? 0;
        $eY = $attributes["y"] ?? 0;
        $eWidth = $attributes["width"] ?? $this->document->getWidth();
        $eHeight = $attributes["height"] ?? $this->document->getHeight();

        // 3. Let align be the align value of preserveAspectRatio, or 'xMidYMid' if preserveAspectRatio is not defined.
        $preserveAspectRatio = explode(" ", $attributes["preserveAspectRatio"] ?? "xMidYMid meet");
        $align = $preserveAspectRatio[0];

        // 4. Let meetOrSlice be the meetOrSlice value of preserveAspectRatio, or 'meet' if preserveAspectRatio is not defined or if meetOrSlice is missing from this value.
        $meetOrSlice = $meetOrSlice ?? "meet";

        // 5. Initialize scale-x to e-width/vb-width.
        $scaleX = $vbWidth == 0 ? 0 : ($eWidth / $vbWidth);

        // 6. Initialize scale-y to e-height/vb-height.
        $scaleY = $vbHeight == 0 ? 0 : ($eHeight / $vbHeight);

        // 7. If align is not 'none' and meetOrSlice is 'meet', set the larger of scale-x and scale-y to the smaller.
        if ($align !== "none" && $meetOrSlice === "meet") {
            $scaleX = min($scaleX, $scaleY);
            $scaleY = min($scaleX, $scaleY);
        }

        // 8. Otherwise, if align is not 'none' and meetOrSlice is 'slice', set the smaller of scale-x and scale-y to the larger.
        elseif ($align !== "none" && $meetOrSlice === "slice") {
            $scaleX = max($scaleX, $scaleY);
            $scaleY = max($scaleX, $scaleY);
        }

        // 9. Initialize translate-x to e-x - (vb-x * scale-x).
        $translateX = $eX - ($vbX * $scaleX);

        // 10. Initialize translate-y to e-y - (vb-y * scale-y)
        $translateY = $eY - ($vbY * $scaleY);

        // 11. If align contains 'xMid', add (e-width - vb-width * scale-x) / 2 to translate-x.
        if (strpos($align, "xMid") !== false) {
            $translateX += ($eWidth - $vbWidth * $scaleX) / 2;
        }

        // 12. If align contains 'xMax', add (e-width - vb-width * scale-x) to translate-x.
        if (strpos($align, "xMax") !== false) {
            $translateX += ($eWidth - $vbWidth * $scaleX);
        }

        // 13. If align contains 'yMid', add (e-height - vb-height * scale-y) / 2 to translate-y.
        if (strpos($align, "yMid") !== false) {
            $translateX += ($eHeight - $vbHeight * $scaleY) / 2;
        }

        // 14. If align contains 'yMax', add (e-height - vb-height * scale-y) to translate-y.
        if (strpos($align, "yMid") !== false) {
            $translateX += ($eHeight - $vbHeight * $scaleY);
        }

        $surface->translate($translateX, $translateY);
        $surface->scale($scaleX, $scaleY);
    }

    /**
     * Convert the given size for the context of this current tag.
     * Takes a pixel-based reference, which is usually specific to the context of the size,
     * but the actual reference size will be decided based upon the unit used.
     *
     * @param string $size
     * @param float $pxReference
     *
     * @return float
     */
    protected function convertSize(string $size, float $pxReference): float
    {
        $length = new CssLength($size);
        $reference = $pxReference;
        $defaultFontSize = 12;

        switch ($length->getUnit()) {
            case "em":
                $reference = $this->style->fontSize ?? $defaultFontSize;
                break;
            case "rem":
                $reference = $this->document->style->fontSize ?? $defaultFontSize;
                break;
            case "ex":
            case "ch":
                $emRef = $this->style->fontSize ?? $defaultFontSize;
                $reference = $emRef * 0.5;
                break;
            case "vw":
                $reference = $this->getDocument()->getWidth();
                break;
            case "vh":
                $reference = $this->getDocument()->getHeight();
                break;
            case "vmin":
                $reference = min($this->getDocument()->getHeight(), $this->getDocument()->getWidth());
                break;
            case "vmax":
                $reference = max($this->getDocument()->getHeight(), $this->getDocument()->getWidth());
                break;
        }

        return (new CssLength($size))->toPixels($reference);
    }
} 
