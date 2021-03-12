<?php

/**
 * This file is part of FPDI
 *
 * @package   setasign\Fpdi
 * @copyright Copyright (c) 2020 Setasign GmbH & Co. KG (https://www.setasign.com)
 * @license   http://opensource.org/licenses/mit-license The MIT License
 */
namespace setasign\Fpdi\PdfParser\Filter;

use setasign\Fpdi\PdfParser\PdfParserException;
/**
 * Exception for filters
 */
class FilterException extends \setasign\Fpdi\PdfParser\PdfParserException
{
    const UNSUPPORTED_FILTER = 0x201;
    const NOT_IMPLEMENTED = 0x202;
}
