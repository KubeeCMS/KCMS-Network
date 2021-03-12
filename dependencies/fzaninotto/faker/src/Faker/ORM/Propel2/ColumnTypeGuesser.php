<?php

namespace Faker\ORM\Propel2;

use WP_Ultimo\Dependencies\Propel\Generator\Model\PropelTypes;
use WP_Ultimo\Dependencies\Propel\Runtime\Map\ColumnMap;
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
    public function guessFormat(\WP_Ultimo\Dependencies\Propel\Runtime\Map\ColumnMap $column)
    {
        $generator = $this->generator;
        if ($column->isTemporal()) {
            if ($column->getType() == \WP_Ultimo\Dependencies\Propel\Generator\Model\PropelTypes::BU_DATE || $column->getType() == \WP_Ultimo\Dependencies\Propel\Generator\Model\PropelTypes::BU_TIMESTAMP) {
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
            case \WP_Ultimo\Dependencies\Propel\Generator\Model\PropelTypes::BOOLEAN:
            case \WP_Ultimo\Dependencies\Propel\Generator\Model\PropelTypes::BOOLEAN_EMU:
                return function () use($generator) {
                    return $generator->boolean;
                };
            case \WP_Ultimo\Dependencies\Propel\Generator\Model\PropelTypes::NUMERIC:
            case \WP_Ultimo\Dependencies\Propel\Generator\Model\PropelTypes::DECIMAL:
                $size = $column->getSize();
                return function () use($generator, $size) {
                    return $generator->randomNumber($size + 2) / 100;
                };
            case \WP_Ultimo\Dependencies\Propel\Generator\Model\PropelTypes::TINYINT:
                return function () {
                    return \mt_rand(0, 127);
                };
            case \WP_Ultimo\Dependencies\Propel\Generator\Model\PropelTypes::SMALLINT:
                return function () {
                    return \mt_rand(0, 32767);
                };
            case \WP_Ultimo\Dependencies\Propel\Generator\Model\PropelTypes::INTEGER:
                return function () {
                    return \mt_rand(0, \intval('2147483647'));
                };
            case \WP_Ultimo\Dependencies\Propel\Generator\Model\PropelTypes::BIGINT:
                return function () {
                    return \mt_rand(0, \intval('9223372036854775807'));
                };
            case \WP_Ultimo\Dependencies\Propel\Generator\Model\PropelTypes::FLOAT:
                return function () {
                    return \mt_rand(0, \intval('2147483647')) / \mt_rand(1, \intval('2147483647'));
                };
            case \WP_Ultimo\Dependencies\Propel\Generator\Model\PropelTypes::DOUBLE:
            case \WP_Ultimo\Dependencies\Propel\Generator\Model\PropelTypes::REAL:
                return function () {
                    return \mt_rand(0, \intval('9223372036854775807')) / \mt_rand(1, \intval('9223372036854775807'));
                };
            case \WP_Ultimo\Dependencies\Propel\Generator\Model\PropelTypes::CHAR:
            case \WP_Ultimo\Dependencies\Propel\Generator\Model\PropelTypes::VARCHAR:
            case \WP_Ultimo\Dependencies\Propel\Generator\Model\PropelTypes::BINARY:
            case \WP_Ultimo\Dependencies\Propel\Generator\Model\PropelTypes::VARBINARY:
                $size = $column->getSize();
                return function () use($generator, $size) {
                    return $generator->text($size);
                };
            case \WP_Ultimo\Dependencies\Propel\Generator\Model\PropelTypes::LONGVARCHAR:
            case \WP_Ultimo\Dependencies\Propel\Generator\Model\PropelTypes::LONGVARBINARY:
            case \WP_Ultimo\Dependencies\Propel\Generator\Model\PropelTypes::CLOB:
            case \WP_Ultimo\Dependencies\Propel\Generator\Model\PropelTypes::CLOB_EMU:
            case \WP_Ultimo\Dependencies\Propel\Generator\Model\PropelTypes::BLOB:
                return function () use($generator) {
                    return $generator->text;
                };
            case \WP_Ultimo\Dependencies\Propel\Generator\Model\PropelTypes::ENUM:
                $valueSet = $column->getValueSet();
                return function () use($generator, $valueSet) {
                    return $generator->randomElement($valueSet);
                };
            case \WP_Ultimo\Dependencies\Propel\Generator\Model\PropelTypes::OBJECT:
            case \WP_Ultimo\Dependencies\Propel\Generator\Model\PropelTypes::PHP_ARRAY:
            default:
                // no smart way to guess what the user expects here
                return null;
        }
    }
}
