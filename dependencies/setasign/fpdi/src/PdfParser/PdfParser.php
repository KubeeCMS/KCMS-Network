<?php

/**
 * This file is part of FPDI
 *
 * @package   setasign\Fpdi
 * @copyright Copyright (c) 2020 Setasign GmbH & Co. KG (https://www.setasign.com)
 * @license   http://opensource.org/licenses/mit-license The MIT License
 */
namespace setasign\Fpdi\PdfParser;

use setasign\Fpdi\PdfParser\CrossReference\CrossReference;
use setasign\Fpdi\PdfParser\CrossReference\CrossReferenceException;
use setasign\Fpdi\PdfParser\Type\PdfArray;
use setasign\Fpdi\PdfParser\Type\PdfBoolean;
use setasign\Fpdi\PdfParser\Type\PdfDictionary;
use setasign\Fpdi\PdfParser\Type\PdfHexString;
use setasign\Fpdi\PdfParser\Type\PdfIndirectObject;
use setasign\Fpdi\PdfParser\Type\PdfIndirectObjectReference;
use setasign\Fpdi\PdfParser\Type\PdfName;
use setasign\Fpdi\PdfParser\Type\PdfNull;
use setasign\Fpdi\PdfParser\Type\PdfNumeric;
use setasign\Fpdi\PdfParser\Type\PdfStream;
use setasign\Fpdi\PdfParser\Type\PdfString;
use setasign\Fpdi\PdfParser\Type\PdfToken;
use setasign\Fpdi\PdfParser\Type\PdfType;
/**
 * A PDF parser class
 */
class PdfParser
{
    /**
     * @var StreamReader
     */
    protected $streamReader;
    /**
     * @var Tokenizer
     */
    protected $tokenizer;
    /**
     * The file header.
     *
     * @var string
     */
    protected $fileHeader;
    /**
     * The offset to the file header.
     *
     * @var int
     */
    protected $fileHeaderOffset;
    /**
     * @var CrossReference|null
     */
    protected $xref;
    /**
     * All read objects.
     *
     * @var array
     */
    protected $objects = [];
    /**
     * PdfParser constructor.
     *
     * @param StreamReader $streamReader
     */
    public function __construct(\setasign\Fpdi\PdfParser\StreamReader $streamReader)
    {
        $this->streamReader = $streamReader;
        $this->tokenizer = new \setasign\Fpdi\PdfParser\Tokenizer($streamReader);
    }
    /**
     * Removes cycled references.
     *
     * @internal
     */
    public function cleanUp()
    {
        $this->xref = null;
    }
    /**
     * Get the stream reader instance.
     *
     * @return StreamReader
     */
    public function getStreamReader()
    {
        return $this->streamReader;
    }
    /**
     * Get the tokenizer instance.
     *
     * @return Tokenizer
     */
    public function getTokenizer()
    {
        return $this->tokenizer;
    }
    /**
     * Resolves the file header.
     *
     * @throws PdfParserException
     * @return int
     */
    protected function resolveFileHeader()
    {
        if ($this->fileHeader) {
            return $this->fileHeaderOffset;
        }
        $this->streamReader->reset(0);
        $maxIterations = 1000;
        while (\true) {
            $buffer = $this->streamReader->getBuffer(\false);
            $offset = \strpos($buffer, '%PDF-');
            if ($offset === \false) {
                if (!$this->streamReader->increaseLength(100) || --$maxIterations === 0) {
                    throw new \setasign\Fpdi\PdfParser\PdfParserException('Unable to find PDF file header.', \setasign\Fpdi\PdfParser\PdfParserException::FILE_HEADER_NOT_FOUND);
                }
                continue;
            }
            break;
        }
        $this->fileHeaderOffset = $offset;
        $this->streamReader->setOffset($offset);
        $this->fileHeader = \trim($this->streamReader->readLine());
        return $this->fileHeaderOffset;
    }
    /**
     * Get the cross reference instance.
     *
     * @return CrossReference
     * @throws CrossReferenceException
     * @throws PdfParserException
     */
    public function getCrossReference()
    {
        if ($this->xref === null) {
            $this->xref = new \setasign\Fpdi\PdfParser\CrossReference\CrossReference($this, $this->resolveFileHeader());
        }
        return $this->xref;
    }
    /**
     * Get the PDF version.
     *
     * @return int[] An array of major and minor version.
     * @throws PdfParserException
     */
    public function getPdfVersion()
    {
        $this->resolveFileHeader();
        if (\preg_match('/%PDF-(\\d)\\.(\\d)/', $this->fileHeader, $result) === 0) {
            throw new \setasign\Fpdi\PdfParser\PdfParserException('Unable to extract PDF version from file header.', \setasign\Fpdi\PdfParser\PdfParserException::PDF_VERSION_NOT_FOUND);
        }
        list(, $major, $minor) = $result;
        $catalog = $this->getCatalog();
        if (isset($catalog->value['Version'])) {
            $versionParts = \explode('.', \setasign\Fpdi\PdfParser\Type\PdfName::unescape(\setasign\Fpdi\PdfParser\Type\PdfType::resolve($catalog->value['Version'], $this)->value));
            if (\count($versionParts) === 2) {
                list($major, $minor) = $versionParts;
            }
        }
        return [(int) $major, (int) $minor];
    }
    /**
     * Get the catalog dictionary.
     *
     * @return PdfDictionary
     * @throws Type\PdfTypeException
     * @throws CrossReferenceException
     * @throws PdfParserException
     */
    public function getCatalog()
    {
        $trailer = $this->getCrossReference()->getTrailer();
        $catalog = \setasign\Fpdi\PdfParser\Type\PdfType::resolve(\setasign\Fpdi\PdfParser\Type\PdfDictionary::get($trailer, 'Root'), $this);
        return \setasign\Fpdi\PdfParser\Type\PdfDictionary::ensure($catalog);
    }
    /**
     * Get an indirect object by its object number.
     *
     * @param int $objectNumber
     * @param bool $cache
     * @return PdfIndirectObject
     * @throws CrossReferenceException
     * @throws PdfParserException
     */
    public function getIndirectObject($objectNumber, $cache = \false)
    {
        $objectNumber = (int) $objectNumber;
        if (isset($this->objects[$objectNumber])) {
            return $this->objects[$objectNumber];
        }
        $object = $this->getCrossReference()->getIndirectObject($objectNumber);
        if ($cache) {
            $this->objects[$objectNumber] = $object;
        }
        return $object;
    }
    /**
     * Read a PDF value.
     *
     * @param null|bool|string $token
     * @param null|string $expectedType
     * @return false|PdfArray|PdfBoolean|PdfDictionary|PdfHexString|PdfIndirectObject|PdfIndirectObjectReference|PdfName|PdfNull|PdfNumeric|PdfStream|PdfString|PdfToken
     * @throws Type\PdfTypeException
     */
    public function readValue($token = null, $expectedType = null)
    {
        if ($token === null) {
            $token = $this->tokenizer->getNextToken();
        }
        if ($token === \false) {
            if ($expectedType !== null) {
                throw new \setasign\Fpdi\PdfParser\Type\PdfTypeException('Got unexpected token type.', \setasign\Fpdi\PdfParser\Type\PdfTypeException::INVALID_DATA_TYPE);
            }
            return \false;
        }
        switch ($token) {
            case '(':
                $this->ensureExpectedType($token, $expectedType);
                return \setasign\Fpdi\PdfParser\Type\PdfString::parse($this->streamReader);
            case '<':
                if ($this->streamReader->getByte() === '<') {
                    $this->ensureExpectedType('<<', $expectedType);
                    $this->streamReader->addOffset(1);
                    return \setasign\Fpdi\PdfParser\Type\PdfDictionary::parse($this->tokenizer, $this->streamReader, $this);
                }
                $this->ensureExpectedType($token, $expectedType);
                return \setasign\Fpdi\PdfParser\Type\PdfHexString::parse($this->streamReader);
            case '/':
                $this->ensureExpectedType($token, $expectedType);
                return \setasign\Fpdi\PdfParser\Type\PdfName::parse($this->tokenizer, $this->streamReader);
            case '[':
                $this->ensureExpectedType($token, $expectedType);
                return \setasign\Fpdi\PdfParser\Type\PdfArray::parse($this->tokenizer, $this);
            default:
                if (\is_numeric($token)) {
                    if (($token2 = $this->tokenizer->getNextToken()) !== \false) {
                        if (\is_numeric($token2) && ($token3 = $this->tokenizer->getNextToken()) !== \false) {
                            switch ($token3) {
                                case 'obj':
                                    if ($expectedType !== null && $expectedType !== \setasign\Fpdi\PdfParser\Type\PdfIndirectObject::class) {
                                        throw new \setasign\Fpdi\PdfParser\Type\PdfTypeException('Got unexpected token type.', \setasign\Fpdi\PdfParser\Type\PdfTypeException::INVALID_DATA_TYPE);
                                    }
                                    return \setasign\Fpdi\PdfParser\Type\PdfIndirectObject::parse((int) $token, (int) $token2, $this, $this->tokenizer, $this->streamReader);
                                case 'R':
                                    if ($expectedType !== null && $expectedType !== \setasign\Fpdi\PdfParser\Type\PdfIndirectObjectReference::class) {
                                        throw new \setasign\Fpdi\PdfParser\Type\PdfTypeException('Got unexpected token type.', \setasign\Fpdi\PdfParser\Type\PdfTypeException::INVALID_DATA_TYPE);
                                    }
                                    return \setasign\Fpdi\PdfParser\Type\PdfIndirectObjectReference::create((int) $token, (int) $token2);
                            }
                            $this->tokenizer->pushStack($token3);
                        }
                        $this->tokenizer->pushStack($token2);
                    }
                    if ($expectedType !== null && $expectedType !== \setasign\Fpdi\PdfParser\Type\PdfNumeric::class) {
                        throw new \setasign\Fpdi\PdfParser\Type\PdfTypeException('Got unexpected token type.', \setasign\Fpdi\PdfParser\Type\PdfTypeException::INVALID_DATA_TYPE);
                    }
                    return \setasign\Fpdi\PdfParser\Type\PdfNumeric::create($token + 0);
                }
                if ($token === 'true' || $token === 'false') {
                    $this->ensureExpectedType($token, $expectedType);
                    return \setasign\Fpdi\PdfParser\Type\PdfBoolean::create($token === 'true');
                }
                if ($token === 'null') {
                    $this->ensureExpectedType($token, $expectedType);
                    return new \setasign\Fpdi\PdfParser\Type\PdfNull();
                }
                if ($expectedType !== null && $expectedType !== \setasign\Fpdi\PdfParser\Type\PdfToken::class) {
                    throw new \setasign\Fpdi\PdfParser\Type\PdfTypeException('Got unexpected token type.', \setasign\Fpdi\PdfParser\Type\PdfTypeException::INVALID_DATA_TYPE);
                }
                $v = new \setasign\Fpdi\PdfParser\Type\PdfToken();
                $v->value = $token;
                return $v;
        }
    }
    /**
     * Ensures that the token will evaluate to an expected object type (or not).
     *
     * @param string $token
     * @param string|null $expectedType
     * @return bool
     * @throws Type\PdfTypeException
     */
    private function ensureExpectedType($token, $expectedType)
    {
        static $mapping = ['(' => \setasign\Fpdi\PdfParser\Type\PdfString::class, '<' => \setasign\Fpdi\PdfParser\Type\PdfHexString::class, '<<' => \setasign\Fpdi\PdfParser\Type\PdfDictionary::class, '/' => \setasign\Fpdi\PdfParser\Type\PdfName::class, '[' => \setasign\Fpdi\PdfParser\Type\PdfArray::class, 'true' => \setasign\Fpdi\PdfParser\Type\PdfBoolean::class, 'false' => \setasign\Fpdi\PdfParser\Type\PdfBoolean::class, 'null' => \setasign\Fpdi\PdfParser\Type\PdfNull::class];
        if ($expectedType === null || $mapping[$token] === $expectedType) {
            return \true;
        }
        throw new \setasign\Fpdi\PdfParser\Type\PdfTypeException('Got unexpected token type.', \setasign\Fpdi\PdfParser\Type\PdfTypeException::INVALID_DATA_TYPE);
    }
}
