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
use WP_Ultimo\Dependencies\ScssPhp\ScssPhp\Ast\Sass\Interpolation;
use WP_Ultimo\Dependencies\ScssPhp\ScssPhp\Ast\Sass\SupportsCondition;
use WP_Ultimo\Dependencies\ScssPhp\ScssPhp\Visitor\StatementSearchVisitor;
/**
 * A visitor for determining whether a {@see MixinRule} recursively contains a
 * {@see ContentRule}.
 *
 * @internal
 *
 * @extends StatementSearchVisitor<bool>
 */
final class HasContentVisitor extends StatementSearchVisitor
{
    public function visitContentRule(ContentRule $node) : bool
    {
        return \true;
    }
    protected function visitArgumentInvocation(ArgumentInvocation $invocation) : ?bool
    {
        return null;
    }
    protected function visitSupportsCondition(SupportsCondition $condition) : ?bool
    {
        return null;
    }
    protected function visitInterpolation(Interpolation $interpolation) : ?bool
    {
        return null;
    }
}
