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
 * A unary operator, as in `+$var` or `not fn()`.
 *
 * @internal
 */
final class UnaryOperationExpression implements Expression
{
    /**
     * @var UnaryOperator::*
     * @readonly
     */
    private $operator;
    /**
     * @var Expression
     * @readonly
     */
    private $operand;
    /**
     * @var FileSpan
     * @readonly
     */
    private $span;
    /**
     * @param UnaryOperator::* $operator
     */
    public function __construct(string $operator, Expression $operand, FileSpan $span)
    {
        $this->operator = $operator;
        $this->operand = $operand;
        $this->span = $span;
    }
    /**
     * @return UnaryOperator::*
     */
    public function getOperator()
    {
        return $this->operator;
    }
    public function getOperand() : Expression
    {
        return $this->operand;
    }
    public function getSpan() : FileSpan
    {
        return $this->span;
    }
    public function accept(ExpressionVisitor $visitor)
    {
        return $visitor->visitUnaryOperationExpression($this);
    }
}
