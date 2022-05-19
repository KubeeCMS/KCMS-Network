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
namespace WP_Ultimo\Dependencies\ScssPhp\ScssPhp\Block;

use WP_Ultimo\Dependencies\ScssPhp\ScssPhp\Block;
use WP_Ultimo\Dependencies\ScssPhp\ScssPhp\Type;
/**
 * @internal
 */
final class ElseifBlock extends Block
{
    /**
     * @var array
     */
    public $cond;
    public function __construct()
    {
        $this->type = Type::T_ELSEIF;
    }
}
