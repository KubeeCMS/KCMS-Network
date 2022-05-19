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

use WP_Ultimo\Dependencies\ScssPhp\ScssPhp\Ast\Sass\ArgumentDeclaration;
use WP_Ultimo\Dependencies\ScssPhp\ScssPhp\Ast\Sass\SassDeclaration;
use WP_Ultimo\Dependencies\ScssPhp\ScssPhp\Ast\Sass\Statement;
use WP_Ultimo\Dependencies\ScssPhp\ScssPhp\SourceSpan\FileSpan;
use WP_Ultimo\Dependencies\ScssPhp\ScssPhp\Util\SpanUtil;
use WP_Ultimo\Dependencies\ScssPhp\ScssPhp\Visitor\StatementVisitor;
/**
 * A mixin declaration.
 *
 * This declares a mixin that's invoked using `@include`.
 *
 * @internal
 */
final class MixinRule extends CallableDeclaration implements SassDeclaration
{
    /**
     * Whether the mixin contains a `@content` rule.
     *
     * @var bool|null
     */
    private $content;
    /**
     * @param Statement[] $children
     */
    public function __construct(string $name, ArgumentDeclaration $arguments, FileSpan $span, array $children, ?SilentComment $comment = null)
    {
        parent::__construct($name, $arguments, $span, $children, $comment);
    }
    public function hasContent() : bool
    {
        if (!isset($this->content)) {
            $this->content = (new HasContentVisitor())->visitMixinRule($this) === \true;
        }
        return $this->content;
    }
    public function getNameSpan() : FileSpan
    {
        $startSpan = $this->getSpan()->getText()[0] === '=' ? SpanUtil::trimLeft($this->getSpan()->subspan(1)) : SpanUtil::withoutInitialAtRule($this->getSpan());
        return SpanUtil::initialIdentifier($startSpan);
    }
    public function accept(StatementVisitor $visitor)
    {
        return $visitor->visitMixinRule($this);
    }
}
