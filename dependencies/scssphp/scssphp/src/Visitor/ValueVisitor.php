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
namespace WP_Ultimo\Dependencies\ScssPhp\ScssPhp\Visitor;

use WP_Ultimo\Dependencies\ScssPhp\ScssPhp\Value\SassBoolean;
use WP_Ultimo\Dependencies\ScssPhp\ScssPhp\Value\SassCalculation;
use WP_Ultimo\Dependencies\ScssPhp\ScssPhp\Value\SassColor;
use WP_Ultimo\Dependencies\ScssPhp\ScssPhp\Value\SassFunction;
use WP_Ultimo\Dependencies\ScssPhp\ScssPhp\Value\SassList;
use WP_Ultimo\Dependencies\ScssPhp\ScssPhp\Value\SassMap;
use WP_Ultimo\Dependencies\ScssPhp\ScssPhp\Value\SassNumber;
use WP_Ultimo\Dependencies\ScssPhp\ScssPhp\Value\SassString;
/**
 * An interface for visitors that traverse SassScript $values.
 *
 * @internal
 *
 * @template T
 */
interface ValueVisitor
{
    /**
     * @return T
     */
    public function visitBoolean(SassBoolean $value);
    /**
     * @return T
     */
    public function visitCalculation(SassCalculation $value);
    /**
     * @return T
     */
    public function visitColor(SassColor $value);
    /**
     * @return T
     */
    public function visitFunction(SassFunction $value);
    /**
     * @return T
     */
    public function visitList(SassList $value);
    /**
     * @return T
     */
    public function visitMap(SassMap $value);
    /**
     * @return T
     */
    public function visitNull();
    /**
     * @return T
     */
    public function visitNumber(SassNumber $value);
    /**
     * @return T
     */
    public function visitString(SassString $value);
}
