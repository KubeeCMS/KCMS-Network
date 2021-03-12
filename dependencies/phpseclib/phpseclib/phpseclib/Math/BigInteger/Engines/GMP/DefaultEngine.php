<?php

/**
 * GMP Modular Exponentiation Engine
 *
 * PHP version 5 and 7
 *
 * @category  Math
 * @package   BigInteger
 * @author    Jim Wigginton <terrafrost@php.net>
 * @copyright 2017 Jim Wigginton
 * @license   http://www.opensource.org/licenses/mit-license.html  MIT License
 * @link      http://pear.php.net/package/Math_BigInteger
 */
namespace phpseclib3\Math\BigInteger\Engines\GMP;

use phpseclib3\Math\BigInteger\Engines\GMP;
/**
 * GMP Modular Exponentiation Engine
 *
 * @package GMP
 * @author  Jim Wigginton <terrafrost@php.net>
 * @access  public
 */
abstract class DefaultEngine extends \phpseclib3\Math\BigInteger\Engines\GMP
{
    /**
     * Performs modular exponentiation.
     *
     * @param GMP $x
     * @param GMP $e
     * @param GMP $n
     * @return GMP
     */
    protected static function powModHelper(\phpseclib3\Math\BigInteger\Engines\GMP $x, \phpseclib3\Math\BigInteger\Engines\GMP $e, \phpseclib3\Math\BigInteger\Engines\GMP $n)
    {
        $temp = new \phpseclib3\Math\BigInteger\Engines\GMP();
        $temp->value = \gmp_powm($x->value, $e->value, $n->value);
        return $x->normalize($temp);
    }
}
