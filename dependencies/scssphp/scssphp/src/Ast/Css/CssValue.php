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
namespace WP_Ultimo\Dependencies\ScssPhp\ScssPhp\Ast\Css;

use WP_Ultimo\Dependencies\ScssPhp\ScssPhp\Ast\AstNode;
use WP_Ultimo\Dependencies\ScssPhp\ScssPhp\SourceSpan\FileSpan;
/**
 * A value in a plain CSS tree.
 *
 * This is used to associate a span with a value that doesn't otherwise track
 * its span.
 *
 * @template T
 *
 * @internal
 */
class CssValue implements AstNode
{
    /**
     * @phpstan-var T
     */
    protected $value;
    /**
     * @var FileSpan
     * @readonly
     */
    private $span;
    /**
     * @param T $value
     */
    public function __construct($value, FileSpan $span)
    {
        $this->value = $value;
        $this->span = $span;
    }
    /**
     * @return T
     */
    public function getValue()
    {
        return $this->value;
    }
    public function getSpan() : FileSpan
    {
        return $this->span;
    }
}
