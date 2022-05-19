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

use WP_Ultimo\Dependencies\ScssPhp\ScssPhp\Ast\Sass\Interpolation;
use WP_Ultimo\Dependencies\ScssPhp\ScssPhp\Ast\Sass\Statement;
use WP_Ultimo\Dependencies\ScssPhp\ScssPhp\SourceSpan\FileSpan;
use WP_Ultimo\Dependencies\ScssPhp\ScssPhp\Visitor\StatementVisitor;
/**
 * A style rule.
 *
 * This applies style declarations to elements that match a given selector.
 *
 * @extends ParentStatement<Statement[]>
 *
 * @internal
 */
final class StyleRule extends ParentStatement
{
    /**
     * @var Interpolation
     * @readonly
     */
    private $selector;
    /**
     * @var FileSpan
     * @readonly
     */
    private $span;
    /**
     * @param Statement[] $children
     */
    public function __construct(Interpolation $selector, array $children, FileSpan $span)
    {
        $this->selector = $selector;
        $this->span = $span;
        parent::__construct($children);
    }
    /**
     * The selector to which the declaration will be applied.
     *
     * This is only parsed after the interpolation has been resolved.
     */
    public function getSelector() : Interpolation
    {
        return $this->selector;
    }
    public function getSpan() : FileSpan
    {
        return $this->span;
    }
    public function accept(StatementVisitor $visitor)
    {
        return $visitor->visitStyleRule($this);
    }
}
