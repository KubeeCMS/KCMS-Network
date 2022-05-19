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
 * A loud CSS-style comment.
 *
 * @internal
 */
final class LoudComment implements Statement
{
    /**
     * @var Interpolation
     * @readonly
     */
    private $text;
    public function __construct(Interpolation $text)
    {
        $this->text = $text;
    }
    public function getText() : Interpolation
    {
        return $this->text;
    }
    public function getSpan() : FileSpan
    {
        return $this->text->getSpan();
    }
    public function accept(StatementVisitor $visitor)
    {
        return $visitor->visitLoudComment($this);
    }
}
