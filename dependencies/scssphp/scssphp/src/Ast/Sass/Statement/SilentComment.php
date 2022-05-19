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

use WP_Ultimo\Dependencies\ScssPhp\ScssPhp\Ast\Sass\Statement;
use WP_Ultimo\Dependencies\ScssPhp\ScssPhp\SourceSpan\FileSpan;
use WP_Ultimo\Dependencies\ScssPhp\ScssPhp\Visitor\StatementVisitor;
/**
 * A silent Sass-style comment.
 *
 * @internal
 */
final class SilentComment implements Statement
{
    /**
     * @var string
     * @readonly
     */
    private $text;
    /**
     * @var FileSpan
     * @readonly
     */
    private $span;
    public function __construct(string $text, FileSpan $span)
    {
        $this->text = $text;
        $this->span = $span;
    }
    public function getText() : string
    {
        return $this->text;
    }
    public function getSpan() : FileSpan
    {
        return $this->span;
    }
    public function accept(StatementVisitor $visitor)
    {
        return $visitor->visitSilentComment($this);
    }
}
