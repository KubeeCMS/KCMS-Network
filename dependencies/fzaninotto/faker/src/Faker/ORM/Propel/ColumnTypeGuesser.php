<?php

namespace Faker\ORM\Propel;

use WP_Ultimo\Dependencies\PropelColumnTypes;
use WP_Ultimo\Dependencies\ColumnMap;
class ColumnTypeGuesser
{
    protected $generator;
    /**
     * @param \Faker\Generator $generator
     */
    public function __construct(\Faker\Generator $generator)
    {
        $this->generator = $generator;
    }
    /**
     * @param ColumnMap $column
     * @return \Closure|null
     */
    public function guessFormat(\WP_Ultimo\Dependencies\ColumnMap $column)
    {
        $generator = $this->generator;
        if ($column->isTemporal()) {
            if ($column->isEpochTemporal()) {
                return function () use($generator) {
                    return $generator->dateTime;
                };
            }
            return function () use($generator) {
                return $generator->dateTimeAD;
            };
        }
        $type = $column->getType();
        switch ($type) {
            case \WP_Ultimo\Dependencies\PropelColumnTypes::BOOLEAN:
            case \WP_Ultimo\Dependencies\PropelColumnTypes::BOOLEAN_EMU:
                return function () use($generator) {
                    return $generator->boolean;
                };
            case \WP_Ultimo\Dependencies\PropelColumnTypes::NUMERIC:
            case \WP_Ultimo\Dependencies\PropelColumnTypes::DECIMAL:
                $size = $column->getSize();
                return function () use($generator, $size) {
                    return $generator->randomNumber($size + 2) / 100;
                };
            case \WP_Ultimo\Dependencies\PropelColumnTypes::TINYINT:
                return function () {
                    return \mt_rand(0, 127);
                };
            case \WP_Ultimo\Dependencies\PropelColumnTypes::SMALLINT:
                return function () {
                    return \mt_rand(0, 32767);
                };
            case \WP_Ultimo\Dependencies\PropelColumnTypes::INTEGER:
                return function () {
                    return \mt_rand(0, \intval('2147483647'));
                };
            case \WP_Ultimo\Dependencies\PropelColumnTypes::BIGINT:
                return function () {
                    return \mt_rand(0, \intval('9223372036854775807'));
                };
            case \WP_Ultimo\Dependencies\PropelColumnTypes::FLOAT:
                return function () {
                    return \mt_rand(0, \intval('2147483647')) / \mt_rand(1, \intval('2147483647'));
                };
            case \WP_Ultimo\Dependencies\PropelColumnTypes::DOUBLE:
            case \WP_Ultimo\Dependencies\PropelColumnTypes::REAL:
                return function () {
                    return \mt_rand(0, \intval('9223372036854775807')) / \mt_rand(1, \intval('9223372036854775807'));
                };
            case \WP_Ultimo\Dependencies\PropelColumnTypes::CHAR:
            case \WP_Ultimo\Dependencies\PropelColumnTypes::VARCHAR:
            case \WP_Ultimo\Dependencies\PropelColumnTypes::BINARY:
            case \WP_Ultimo\Dependencies\PropelColumnTypes::VARBINARY:
                $size = $column->getSize();
                return function () use($generator, $size) {
                    return $generator->text($size);
                };
            case \WP_Ultimo\Dependencies\PropelColumnTypes::LONGVARCHAR:
            case \WP_Ultimo\Dependencies\PropelColumnTypes::LONGVARBINARY:
            case \WP_Ultimo\Dependencies\PropelColumnTypes::CLOB:
            case \WP_Ultimo\Dependencies\PropelColumnTypes::CLOB_EMU:
            case \WP_Ultimo\Dependencies\PropelColumnTypes::BLOB:
                return function () use($generator) {
                    return $generator->text;
                };
            case \WP_Ultimo\Dependencies\PropelColumnTypes::ENUM:
                $valueSet = $column->getValueSet();
                return function () use($generator, $valueSet) {
                    return $generator->randomElement($valueSet);
                };
            case \WP_Ultimo\Dependencies\PropelColumnTypes::OBJECT:
            case \WP_Ultimo\Dependencies\PropelColumnTypes::PHP_ARRAY:
            default:
                // no smart way to guess what the user expects here
                return null;
        }
    }
}
