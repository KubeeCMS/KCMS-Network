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
use setasign\Fpdi\PdfParser\Type\PdfIndirectObject;
use setasign\Fpdi\PdfParser\Type\PdfNumeric;
use setasign\Fpdi\PdfParser\Type\PdfStream;
use setasign\Fpdi\PdfParser\Type\PdfToken;
use setasign\Fpdi\PdfParser\Type\PdfTypeException;
/**
 * Class CrossReference
 *
 * This class processes the standard cross reference of a PDF document.
 */
class CrossReference
{
    /**
     * The byte length in which the "startxref" keyword should be searched.
     *
     * @var int
     */
    public static $trailerSearchLength = 5500;
    /**
     * @var int
     */
    protected $fileHeaderOffset = 0;
    /**
     * @var PdfParser
     */
    protected $parser;
    /**
     * @var ReaderInterface[]
     */
    protected $readers = [];
    /**
     * CrossReference constructor.
     *
     * @param PdfParser $parser
     * @throws CrossReferenceException
     * @throws PdfTypeException
     */
    public function __construct(\setasign\Fpdi\PdfParser\PdfParser $parser, $fileHeaderOffset = 0)
    {
        $this->parser = $parser;
        $this->fileHeaderOffset = $fileHeaderOffset;
        $offset = $this->findStartXref();
        $reader = null;
        /** @noinspection TypeUnsafeComparisonInspection */
        while ($offset != \false) {
            // By doing an unsafe comparsion we ignore faulty references to byte offset 0
            try {
                $reader = $this->readXref($offset + $this->fileHeaderOffset);
            } catch (\setasign\Fpdi\PdfParser\CrossReference\CrossReferenceException $e) {
                // sometimes the file header offset is part of the byte offsets, so let's retry by resetting it to zero.
                if ($e->getCode() === \setasign\Fpdi\PdfParser\CrossReference\CrossReferenceException::INVALID_DATA && $this->fileHeaderOffset !== 0) {
                    $this->fileHeaderOffset = 0;
                    $reader = $this->readXref($offset + $this->fileHeaderOffset);
                } else {
                    throw $e;
                }
            }
            $trailer = $reader->getTrailer();
            $this->checkForEncryption($trailer);
            $this->readers[] = $reader;
            if (isset($trailer->value['Prev'])) {
                $offset = $trailer->value['Prev']->value;
            } else {
                $offset = \false;
            }
        }
        // fix faulty sub-section header
        if ($reader instanceof \setasign\Fpdi\PdfParser\CrossReference\FixedReader) {
            /**
             * @var FixedReader $reader
             */
            $reader->fixFaultySubSectionShift();
        }
        if ($reader === null) {
            throw new \setasign\Fpdi\PdfParser\CrossReference\CrossReferenceException('No cross-reference found.', \setasign\Fpdi\PdfParser\CrossReference\CrossReferenceException::NO_XREF_FOUND);
        }
    }
    /**
     * Get the size of the cross reference.
     *
     * @return integer
     */
    public function getSize()
    {
        return $this->getTrailer()->value['Size']->value;
    }
    /**
     * Get the trailer dictionary.
     *
     * @return PdfDictionary
     */
    public function getTrailer()
    {
        return $this->readers[0]->getTrailer();
    }
    /**
     * Get the cross reference readser instances.
     *
     * @return ReaderInterface[]
     */
    public function getReaders()
    {
        return $this->readers;
    }
    /**
     * Get the offset by an object number.
     *
     * @param int $objectNumber
     * @return integer|bool
     */
    public function getOffsetFor($objectNumber)
    {
        foreach ($this->getReaders() as $reader) {
            $offset = $reader->getOffsetFor($objectNumber);
            if ($offset !== \false) {
                return $offset;
            }
        }
        return \false;
    }
    /**
     * Get an indirect object by its object number.
     *
     * @param int $objectNumber
     * @return PdfIndirectObject
     * @throws CrossReferenceException
     */
    public function getIndirectObject($objectNumber)
    {
        $offset = $this->getOffsetFor($objectNumber);
        if ($offset === \false) {
            throw new \setasign\Fpdi\PdfParser\CrossReference\CrossReferenceException(\sprintf('Object (id:%s) not found.', $objectNumber), \setasign\Fpdi\PdfParser\CrossReference\CrossReferenceException::OBJECT_NOT_FOUND);
        }
        $parser = $this->parser;
        $parser->getTokenizer()->clearStack();
        $parser->getStreamReader()->reset($offset + $this->fileHeaderOffset);
        try {
            /** @var PdfIndirectObject $object */
            $object = $parser->readValue(null, \setasign\Fpdi\PdfParser\Type\PdfIndirectObject::class);
        } catch (\setasign\Fpdi\PdfParser\Type\PdfTypeException $e) {
            throw new \setasign\Fpdi\PdfParser\CrossReference\CrossReferenceException(\sprintf('Object (id:%s) not found at location (%s).', $objectNumber, $offset), \setasign\Fpdi\PdfParser\CrossReference\CrossReferenceException::OBJECT_NOT_FOUND, $e);
        }
        if ($object->objectNumber !== $objectNumber) {
            throw new \setasign\Fpdi\PdfParser\CrossReference\CrossReferenceException(\sprintf('Wrong object found, got %s while %s was expected.', $object->objectNumber, $objectNumber), \setasign\Fpdi\PdfParser\CrossReference\CrossReferenceException::OBJECT_NOT_FOUND);
        }
        return $object;
    }
    /**
     * Read the cross-reference table at a given offset.
     *
     * Internally the method will try to evaluate the best reader for this cross-reference.
     *
     * @param int $offset
     * @return ReaderInterface
     * @throws CrossReferenceException
     * @throws PdfTypeException
     */
    protected function readXref($offset)
    {
        $this->parser->getStreamReader()->reset($offset);
        $this->parser->getTokenizer()->clearStack();
        $initValue = $this->parser->readValue();
        return $this->initReaderInstance($initValue);
    }
    /**
     * Get a cross-reference reader instance.
     *
     * @param PdfToken|PdfIndirectObject $initValue
     * @return ReaderInterface|bool
     * @throws CrossReferenceException
     * @throws PdfTypeException
     */
    protected function initReaderInstance($initValue)
    {
        $position = $this->parser->getStreamReader()->getPosition() + $this->parser->getStreamReader()->getOffset() + $this->fileHeaderOffset;
        if ($initValue instanceof \setasign\Fpdi\PdfParser\Type\PdfToken && $initValue->value === 'xref') {
            try {
                return new \setasign\Fpdi\PdfParser\CrossReference\FixedReader($this->parser);
            } catch (\setasign\Fpdi\PdfParser\CrossReference\CrossReferenceException $e) {
                $this->parser->getStreamReader()->reset($position);
                $this->parser->getTokenizer()->clearStack();
                return new \setasign\Fpdi\PdfParser\CrossReference\LineReader($this->parser);
            }
        }
        if ($initValue instanceof \setasign\Fpdi\PdfParser\Type\PdfIndirectObject) {
            try {
                $stream = \setasign\Fpdi\PdfParser\Type\PdfStream::ensure($initValue->value);
            } catch (\setasign\Fpdi\PdfParser\Type\PdfTypeException $e) {
                throw new \setasign\Fpdi\PdfParser\CrossReference\CrossReferenceException('Invalid object type at xref reference offset.', \setasign\Fpdi\PdfParser\CrossReference\CrossReferenceException::INVALID_DATA, $e);
            }
            $type = \setasign\Fpdi\PdfParser\Type\PdfDictionary::get($stream->value, 'Type');
            if ($type->value !== 'XRef') {
                throw new \setasign\Fpdi\PdfParser\CrossReference\CrossReferenceException('The xref position points to an incorrect object type.', \setasign\Fpdi\PdfParser\CrossReference\CrossReferenceException::INVALID_DATA);
            }
            $this->checkForEncryption($stream->value);
            throw new \setasign\Fpdi\PdfParser\CrossReference\CrossReferenceException('This PDF document probably uses a compression technique which is not supported by the ' . 'free parser shipped with FPDI. (See https://www.setasign.com/fpdi-pdf-parser for more details)', \setasign\Fpdi\PdfParser\CrossReference\CrossReferenceException::COMPRESSED_XREF);
        }
        throw new \setasign\Fpdi\PdfParser\CrossReference\CrossReferenceException('The xref position points to an incorrect object type.', \setasign\Fpdi\PdfParser\CrossReference\CrossReferenceException::INVALID_DATA);
    }
    /**
     * Check for encryption.
     *
     * @param PdfDictionary $dictionary
     * @throws CrossReferenceException
     */
    protected function checkForEncryption(\setasign\Fpdi\PdfParser\Type\PdfDictionary $dictionary)
    {
        if (isset($dictionary->value['Encrypt'])) {
            throw new \setasign\Fpdi\PdfParser\CrossReference\CrossReferenceException('This PDF document is encrypted and cannot be processed with FPDI.', \setasign\Fpdi\PdfParser\CrossReference\CrossReferenceException::ENCRYPTED);
        }
    }
    /**
     * Find the start position for the first cross-reference.
     *
     * @return int The byte-offset position of the first cross-reference.
     * @throws CrossReferenceException
     */
    protected function findStartXref()
    {
        $reader = $this->parser->getStreamReader();
        $reader->reset(-self::$trailerSearchLength, self::$trailerSearchLength);
        $buffer = $reader->getBuffer(\false);
        $pos = \strrpos($buffer, 'startxref');
        $addOffset = 9;
        if ($pos === \false) {
            // Some corrupted documents uses startref, instead of startxref
            $pos = \strrpos($buffer, 'startref');
            if ($pos === \false) {
                throw new \setasign\Fpdi\PdfParser\CrossReference\CrossReferenceException('Unable to find pointer to xref table', \setasign\Fpdi\PdfParser\CrossReference\CrossReferenceException::NO_STARTXREF_FOUND);
            }
            $addOffset = 8;
        }
        $reader->setOffset($pos + $addOffset);
        try {
            $value = $this->parser->readValue(null, \setasign\Fpdi\PdfParser\Type\PdfNumeric::class);
        } catch (\setasign\Fpdi\PdfParser\Type\PdfTypeException $e) {
            throw new \setasign\Fpdi\PdfParser\CrossReference\CrossReferenceException('Invalid data after startxref keyword.', \setasign\Fpdi\PdfParser\CrossReference\CrossReferenceException::INVALID_DATA, $e);
        }
        return $value->value;
    }
}
