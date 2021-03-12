<?php

/**
 * Curve
 *
 * PHP version 5
 *
 * @category  File
 * @package   ASN1
 * @author    Jim Wigginton <terrafrost@php.net>
 * @copyright 2016 Jim Wigginton
 * @license   http://www.opensource.org/licenses/mit-license.html  MIT License
 * @link      http://phpseclib.sourceforge.net
 */
namespace phpseclib3\File\ASN1\Maps;

use phpseclib3\File\ASN1;
/**
 * Curve
 *
 * @package ASN1
 * @author  Jim Wigginton <terrafrost@php.net>
 * @access  public
 */
abstract class Curve
{
    const MAP = ['type' => \phpseclib3\File\ASN1::TYPE_SEQUENCE, 'children' => ['a' => \phpseclib3\File\ASN1\Maps\FieldElement::MAP, 'b' => \phpseclib3\File\ASN1\Maps\FieldElement::MAP, 'seed' => ['type' => \phpseclib3\File\ASN1::TYPE_BIT_STRING, 'optional' => \true]]];
}
