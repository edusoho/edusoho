<?php
/**
 * @package php-svg-lib
 * @link    http://github.com/PhenX/php-svg-lib
 * @author  Fabien Ménager <fabien.menager@gmail.com>
 * @license GNU LGPLv3+ http://www.gnu.org/copyleft/lesser.html
 */

namespace Svg\Tag;

class UseTag extends AbstractTag
{
    protected $x = 0;
    protected $y = 0;
    protected $width;
    protected $height;
    protected $instances = 0;

    /** @var AbstractTag */
    protected $reference;

    protected function before($attributes)
    {
        $this->instances++;
        if ($this->instances > 1) {
            //TODO: log circular reference error state
            return;
        }

        if (isset($attributes['x'])) {
            $this->x = $attributes['x'];
        }
        if (isset($attributes['y'])) {
            $this->y = $attributes['y'];
        }

        if (isset($attributes['width'])) {
            $this->width = $attributes['width'];
        }
        if (isset($attributes['height'])) {
            $this->height = $attributes['height'];
        }

        parent::before($attributes);

        $document = $this->getDocument();

        $link = $attributes["href"] ?? $attributes["xlink:href"];
        $this->reference = $document->getDef($link);

        $surface = $document->getSurface();
        $surface->save();

        $surface->translate($this->x, $this->y);
    }

    protected function after() {
        if ($this->instances > 0) {
            return;
        }
        parent::after();
        $this->getDocument()->getSurface()->restore();
    }

    public function handle($attributes)
    {
        if ($this->instances > 1) {
            //TODO: log circular reference error state
            return;
        }

        parent::handle($attributes);

        if (!$this->reference) {
            return;
        }

        $originalAttributes = array_merge($this->reference->attributes);
        $originalStyle = $this->reference->getStyle();
        $mergedAttributes = $this->reference->attributes;
        $attributesToNotMerge = ['x', 'y', 'width', 'height', 'href', 'xlink:href', 'id', 'style'];
        foreach ($attributes as $attrKey => $attrVal) {
            if (!in_array($attrKey, $attributesToNotMerge) && !isset($mergedAttributes[$attrKey])) {
                $mergedAttributes[$attrKey] = $attrVal;
            }
        }
        $mergedAttributes['style'] = ($attributes['style'] ?? '') . ';' . ($mergedAttributes['style'] ?? '');

        $this->_handle($this->reference, $mergedAttributes);

        $this->reference->attributes = $originalAttributes;
        if ($originalStyle !== null) {
            $this->reference->setStyle($originalStyle);
        }
    }

    public function handleEnd()
    {
        $this->instances--;
        if ($this->instances > 0) {
            return;
        }

        if ($this->reference) {
            $this->_handleEnd($this->reference);
        }

        parent::handleEnd();
    }

    private function _handle($tag, $attributes) {
        $tag->handle($attributes);
        foreach ($tag->children as $child) {
            $originalAttributes = array_merge($child->attributes);
            $originalStyle = $child->getStyle();
            $mergedAttributes = $child->attributes;
            $mergedAttributes['style'] = ($attributes['style'] ?? '') . ';' . ($mergedAttributes['style'] ?? '');
            $this->_handle($child, $mergedAttributes);
            $child->attributes = $originalAttributes;
            if ($originalStyle !== null) {
                $child->setStyle($originalStyle);
            }
        }
    }

    private function _handleEnd($tag) {
        foreach ($tag->children as $child) {
            $this->_handleEnd($child);
        }
        $tag->handleEnd();
    }
} 
