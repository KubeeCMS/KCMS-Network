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

use WP_Ultimo\Dependencies\ScssPhp\ScssPhp\Ast\Sass\ArgumentInvocation;
use WP_Ultimo\Dependencies\ScssPhp\ScssPhp\Ast\Sass\CallableInvocation;
use WP_Ultimo\Dependencies\ScssPhp\ScssPhp\Ast\Sass\Expression;
use WP_Ultimo\Dependencies\ScssPhp\ScssPhp\Ast\Sass\Interpolation;
use WP_Ultimo\Dependencies\ScssPhp\ScssPhp\SourceSpan\FileSpan;
use WP_Ultimo\Dependencies\ScssPhp\ScssPhp\Visitor\ExpressionVisitor;
/**
 * An interpolated function invocation.
 *
 * This is always a plain CSS function.
 *
 * @internal
 */
final class InterpolatedFunctionExpression implements Expression, CallableInvocation
{
    /**
     * The name of the function being invoked.
     *
     * @var Interpolation
     * @readonly
     */
    private $name;
    /**
     * The arguments to pass to the function.
     *
     * @var ArgumentInvocation
     * @readonly
     */
    private $arguments;
    /**
     * @var FileSpan
     * @readonly
     */
    private $span;
    public function __construct(Interpolation $name, ArgumentInvocation $arguments, FileSpan $span)
    {
        $this->span = $span;
        $this->name = $name;
        $this->arguments = $arguments;
    }
    public function getName() : Interpolation
    {
        return $this->name;
    }
    public function getArguments() : ArgumentInvocation
    {
        return $this->arguments;
    }
    public function getSpan() : FileSpan
    {
        return $this->span;
    }
    public function accept(ExpressionVisitor $visitor)
    {
        return $visitor->visitInterpolatedFunctionExpression($this);
    }
}
