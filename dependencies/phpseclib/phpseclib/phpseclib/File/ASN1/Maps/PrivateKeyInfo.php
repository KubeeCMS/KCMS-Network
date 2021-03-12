<?php

/**
 * PrivateKeyInfo
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
 * PrivateKeyInfo
 *
 * @package ASN1
 * @author  Jim Wigginton <terrafrost@php.net>
 * @access  public
 */
abstract class PrivateKeyInfo
{
    const MAP = ['type' => \phpseclib3\File\ASN1::TYPE_SEQUENCE, 'children' => ['version' => ['type' => \phpseclib3\File\ASN1::TYPE_INTEGER, 'mapping' => ['v1']], 'privateKeyAlgorithm' => \phpseclib3\File\ASN1\Maps\AlgorithmIdentifier::MAP, 'privateKey' => \phpseclib3\File\ASN1\Maps\PrivateKey::MAP, 'attributes' => ['constant' => 0, 'optional' => \true, 'implicit' => \true] + \phpseclib3\File\ASN1\Maps\Attributes::MAP]];
}
