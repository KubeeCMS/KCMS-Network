<?php

/**
 * SCSSPHP
 *
 * @copyright 2012-2020 Leaf Corcoran
 *
 * @license http://opensource.org/licenses/MIT MIT
 *
 * @link http://scssphp.github.io/scssphp
 */
namespace WP_Ultimo\Dependencies\ScssPhp\ScssPhp\Ast\Sass\Expression;

use WP_Ultimo\Dependencies\ScssPhp\ScssPhp\Ast\Sass\Expression;
use WP_Ultimo\Dependencies\ScssPhp\ScssPhp\SourceSpan\FileSpan;
use WP_Ultimo\Dependencies\ScssPhp\ScssPhp\Visitor\ExpressionVisitor;
/**
 * A map literal.
 *
 * @internal
 */
final class MapExpression implements Expression
{
    /**
     * @var list<array{Expression, Expression}>
     * @readonly
     */
    private $pairs;
    /**
     * @var FileSpan
     * @readonly
     */
    private $span;
    /**
     * @param list<array{Expression, Expression}> $pairs
     */
    public function __construct(array $pairs, FileSpan $span)
    {
        $this->pairs = $pairs;
        $this->span = $span;
    }
    /**
     * @return list<array{Expression, Expression}>
     */
    public function getPairs() : array
    {
        return $this->pairs;
    }
    public function getSpan() : FileSpan
    {
        return $this->span;
    }
    public function accept(ExpressionVisitor $visitor)
    {
        return $visitor->visitMapExpression($this);
    }
}
