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

use WP_Ultimo\Dependencies\ScssPhp\ScssPhp\Ast\Sass\ArgumentInvocation;
use WP_Ultimo\Dependencies\ScssPhp\ScssPhp\Ast\Sass\CallableInvocation;
use WP_Ultimo\Dependencies\ScssPhp\ScssPhp\Ast\Sass\SassReference;
use WP_Ultimo\Dependencies\ScssPhp\ScssPhp\Ast\Sass\Statement;
use WP_Ultimo\Dependencies\ScssPhp\ScssPhp\SourceSpan\FileSpan;
use WP_Ultimo\Dependencies\ScssPhp\ScssPhp\Util\SpanUtil;
use WP_Ultimo\Dependencies\ScssPhp\ScssPhp\Visitor\StatementVisitor;
/**
 * A mixin invocation.
 *
 * @internal
 */
final class IncludeRule implements Statement, CallableInvocation, SassReference
{
    /**
     * @var string|null
     * @readonly
     */
    private $namespace;
    /**
     * @var string
     * @readonly
     */
    private $name;
    /**
     * @var ArgumentInvocation
     * @readonly
     */
    private $arguments;
    /**
     * @var ContentBlock|null
     * @readonly
     */
    private $content;
    /**
     * @var FileSpan
     * @readonly
     */
    private $span;
    public function __construct(string $name, ArgumentInvocation $arguments, FileSpan $span, ?string $namespace = null, ?ContentBlock $content = null)
    {
        $this->name = $name;
        $this->arguments = $arguments;
        $this->span = $span;
        $this->namespace = $namespace;
        $this->content = $content;
    }
    public function getNamespace() : ?string
    {
        return $this->namespace;
    }
    public function getName() : string
    {
        return $this->name;
    }
    public function getArguments() : ArgumentInvocation
    {
        return $this->arguments;
    }
    public function getContent() : ?ContentBlock
    {
        return $this->content;
    }
    public function getSpan() : FileSpan
    {
        return $this->span;
    }
    public function getNameSpan() : FileSpan
    {
        $startSpan = $this->span->getText()[0] === '+' ? SpanUtil::trimLeft($this->span->subspan(1)) : SpanUtil::withoutInitialAtRule($this->span);
        if ($this->namespace !== null) {
            $startSpan = SpanUtil::withoutNamespace($startSpan);
        }
        return SpanUtil::initialIdentifier($startSpan);
    }
    public function getNamespaceSpan() : ?FileSpan
    {
        if ($this->namespace === null) {
            return null;
        }
        $startSpan = $this->span->getText()[0] === '+' ? SpanUtil::trimLeft($this->span->subspan(1)) : SpanUtil::withoutInitialAtRule($this->span);
        return SpanUtil::initialIdentifier($startSpan);
    }
    public function accept(StatementVisitor $visitor)
    {
        return $visitor->visitIncludeRule($this);
    }
}
