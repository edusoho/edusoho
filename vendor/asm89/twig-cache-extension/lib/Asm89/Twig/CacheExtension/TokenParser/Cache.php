<?php

/*
 * This file is part of twig-cache-extension.
 *
 * (c) Alexander <iam.asm89@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Asm89\Twig\CacheExtension\TokenParser;

use Asm89\Twig\CacheExtension\Node\CacheNode;

/**
 * Parser for cache/endcache blocks.
 *
 * @author Alexander <iam.asm89@gmail.com>
 */
class Cache extends \Twig_TokenParser
{
    /**
     * @param \Twig_Token $token
     *
     * @return boolean
     */
    public function decideCacheEnd(\Twig_Token $token)
    {
        return $token->test('endcache');
    }

    /**
     * {@inheritDoc}
     */
    public function getTag()
    {
        return 'cache';
    }

    /**
     * {@inheritDoc}
     */
    public function parse(\Twig_Token $token)
    {
        $lineno = $token->getLine();
        $stream = $this->parser->getStream();

        $annotation = $this->parser->getExpressionParser()->parseExpression();

        $key = $this->parser->getExpressionParser()->parseExpression();

        $stream->expect(\Twig_Token::BLOCK_END_TYPE);
        $body = $this->parser->subparse(array($this, 'decideCacheEnd'), true);
        $stream->expect(\Twig_Token::BLOCK_END_TYPE);

        return new CacheNode($annotation, $key, $body, $lineno, $this->getTag());
    }
}
