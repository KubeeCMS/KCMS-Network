<?php

/**
 * This file is part of FPDI
 *
 * @package   setasign\Fpdi
 * @copyright Copyright (c) 2020 Setasign GmbH & Co. KG (https://www.setasign.com)
 * @license   http://opensource.org/licenses/mit-license The MIT License
 */
namespace setasign\Fpdi\PdfParser\CrossReference;

use setasign\Fpdi\PdfParser\PdfParser;
use setasign\Fpdi\PdfParser\Type\PdfDictionary;
use setasign\Fpdi\PdfParser\Type\PdfToken;
use setasign\Fpdi\PdfParser\Type\PdfTypeException;
/**
 * Abstract class for cross-reference reader classes.
 */
abstract class AbstractReader
{
    /**
     * @var PdfParser
     */
    protected $parser;
    /**
     * @var PdfDictionary
     */
    protected $trailer;
    /**
     * AbstractReader constructor.
     *
     * @param PdfParser $parser
     * @throws CrossReferenceException
     * @throws PdfTypeException
     */
    public function __construct(\setasign\Fpdi\PdfParser\PdfParser $parser)
    {
        $this->parser = $parser;
        $this->readTrailer();
    }
    /**
     * Get the trailer dictionary.
     *
     * @return PdfDictionary
     */
    public function getTrailer()
    {
        return $this->trailer;
    }
    /**
     * Read the trailer dictionary.
     *
     * @throws CrossReferenceException
     * @throws PdfTypeException
     */
    protected function readTrailer()
    {
        try {
            $trailerKeyword = $this->parser->readValue(null, \setasign\Fpdi\PdfParser\Type\PdfToken::class);
            if ($trailerKeyword->value !== 'trailer') {
                throw new \setasign\Fpdi\PdfParser\CrossReference\CrossReferenceException(\sprintf('Unexpected end of cross reference. "trailer"-keyword expected, got: %s.', $trailerKeyword->value), \setasign\Fpdi\PdfParser\CrossReference\CrossReferenceException::UNEXPECTED_END);
            }
        } catch (\setasign\Fpdi\PdfParser\Type\PdfTypeException $e) {
            throw new \setasign\Fpdi\PdfParser\CrossReference\CrossReferenceException('Unexpected end of cross reference. "trailer"-keyword expected, got an invalid object type.', \setasign\Fpdi\PdfParser\CrossReference\CrossReferenceException::UNEXPECTED_END, $e);
        }
        try {
            $trailer = $this->parser->readValue(null, \setasign\Fpdi\PdfParser\Type\PdfDictionary::class);
        } catch (\setasign\Fpdi\PdfParser\Type\PdfTypeException $e) {
            throw new \setasign\Fpdi\PdfParser\CrossReference\CrossReferenceException('Unexpected end of cross reference. Trailer not found.', \setasign\Fpdi\PdfParser\CrossReference\CrossReferenceException::UNEXPECTED_END, $e);
        }
        $this->trailer = $trailer;
    }
}
