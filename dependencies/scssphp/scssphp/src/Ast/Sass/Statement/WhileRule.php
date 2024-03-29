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
namespace WP_Ultimo\Dependencies\ScssPhp\ScssPhp\Ast\Sass\Statement;

use WP_Ultimo\Dependencies\ScssPhp\ScssPhp\Ast\Sass\Expression;
use WP_Ultimo\Dependencies\ScssPhp\ScssPhp\Ast\Sass\Statement;
use WP_Ultimo\Dependencies\ScssPhp\ScssPhp\SourceSpan\FileSpan;
use WP_Ultimo\Dependencies\ScssPhp\ScssPhp\Visitor\StatementVisitor;
/**
 * A `@while` rule.
 *
 * This repeatedly executes a block of code as long as a statement evaluates to
 * `true`.
 *
 * @extends ParentStatement<Statement[]>
 *
 * @internal
 */
final class WhileRule extends ParentStatement
{
    /**
     * @var Expression
     * @readonly
     */
    private $condition;
    /**
     * @var FileSpan
     * @readonly
     */
    private $span;
    /**
     * @param Statement[] $children
     */
    public function __construct(Expression $condition, array $children, FileSpan $span)
    {
        $this->condition = $condition;
        $this->span = $span;
        parent::__construct($children);
    }
    public function getCondition() : Expression
    {
        return $this->condition;
    }
    public function getSpan() : FileSpan
    {
        return $this->span;
    }
    public function accept(StatementVisitor $visitor)
    {
        return $visitor->visitWhileRule($this);
    }
}
