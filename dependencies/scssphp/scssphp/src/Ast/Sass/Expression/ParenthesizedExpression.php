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
 * An expression wrapped in parentheses.
 *
 * @internal
 */
final class ParenthesizedExpression implements Expression
{
    /**
     * @var Expression
     * @readonly
     */
    private $expression;
    /**
     * @var FileSpan
     * @readonly
     */
    private $span;
    public function __construct(Expression $expression, FileSpan $span)
    {
        $this->expression = $expression;
        $this->span = $span;
    }
    public function getExpression() : Expression
    {
        return $this->expression;
    }
    public function getSpan() : FileSpan
    {
        return $this->span;
    }
    public function accept(ExpressionVisitor $visitor)
    {
        return $visitor->visitParenthesizedExpression($this);
    }
}
