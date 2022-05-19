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

use WP_Ultimo\Dependencies\ScssPhp\ScssPhp\Ast\Sass\SassDeclaration;
use WP_Ultimo\Dependencies\ScssPhp\ScssPhp\SourceSpan\FileSpan;
use WP_Ultimo\Dependencies\ScssPhp\ScssPhp\Util\SpanUtil;
use WP_Ultimo\Dependencies\ScssPhp\ScssPhp\Visitor\StatementVisitor;
/**
 * A function declaration.
 *
 * This declares a function that's invoked using normal CSS function syntax.
 *
 * @internal
 */
final class FunctionRule extends CallableDeclaration implements SassDeclaration
{
    public function getNameSpan() : FileSpan
    {
        return SpanUtil::initialIdentifier(SpanUtil::withoutInitialAtRule($this->getSpan()));
    }
    public function accept(StatementVisitor $visitor)
    {
        return $visitor->visitFunctionRule($this);
    }
}
