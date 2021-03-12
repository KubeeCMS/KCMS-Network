<?php

/**
 * PolicyInformation
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
 * PolicyInformation
 *
 * @package ASN1
 * @author  Jim Wigginton <terrafrost@php.net>
 * @access  public
 */
abstract class PolicyInformation
{
    const MAP = ['type' => \phpseclib3\File\ASN1::TYPE_SEQUENCE, 'children' => ['policyIdentifier' => \phpseclib3\File\ASN1\Maps\CertPolicyId::MAP, 'policyQualifiers' => ['type' => \phpseclib3\File\ASN1::TYPE_SEQUENCE, 'min' => 0, 'max' => -1, 'optional' => \true, 'children' => \phpseclib3\File\ASN1\Maps\PolicyQualifierInfo::MAP]]];
}
