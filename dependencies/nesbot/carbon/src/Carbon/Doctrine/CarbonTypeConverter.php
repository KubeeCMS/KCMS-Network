<?php

/**
 * Thanks to https://github.com/flaushi for his suggestion:
 * https://github.com/doctrine/dbal/issues/2873#issuecomment-534956358
 */
namespace WP_Ultimo\Dependencies\Carbon\Doctrine;

use WP_Ultimo\Dependencies\Carbon\Carbon;
use WP_Ultimo\Dependencies\Carbon\CarbonInterface;
use DateTimeInterface;
use WP_Ultimo\Dependencies\Doctrine\DBAL\Platforms\AbstractPlatform;
use WP_Ultimo\Dependencies\Doctrine\DBAL\Types\ConversionException;
use Exception;
trait CarbonTypeConverter
{
    protected function getCarbonClassName() : string
    {
        return \WP_Ultimo\Dependencies\Carbon\Carbon::class;
    }
    public function getSQLDeclaration(array $fieldDeclaration, \WP_Ultimo\Dependencies\Doctrine\DBAL\Platforms\AbstractPlatform $platform)
    {
        $precision = ($fieldDeclaration['precision'] ?: 10) === 10 ? \WP_Ultimo\Dependencies\Carbon\Doctrine\DateTimeDefaultPrecision::get() : $fieldDeclaration['precision'];
        $type = parent::getSQLDeclaration($fieldDeclaration, $platform);
        if (!$precision) {
            return $type;
        }
        if (\strpos($type, '(') !== \false) {
            return \preg_replace('/\\(\\d+\\)/', "({$precision})", $type);
        }
        list($before, $after) = \explode(' ', "{$type} ");
        return \trim("{$before}({$precision}) {$after}");
    }
    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function convertToPHPValue($value, \WP_Ultimo\Dependencies\Doctrine\DBAL\Platforms\AbstractPlatform $platform)
    {
        $class = $this->getCarbonClassName();
        if ($value === null || \is_a($value, $class)) {
            return $value;
        }
        if ($value instanceof \DateTimeInterface) {
            return $class::instance($value);
        }
        $date = null;
        $error = null;
        try {
            $date = $class::parse($value);
        } catch (\Exception $exception) {
            $error = $exception;
        }
        if (!$date) {
            throw \WP_Ultimo\Dependencies\Doctrine\DBAL\Types\ConversionException::conversionFailedFormat($value, $this->getName(), 'Y-m-d H:i:s.u or any format supported by ' . $class . '::parse()', $error);
        }
        return $date;
    }
    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function convertToDatabaseValue($value, \WP_Ultimo\Dependencies\Doctrine\DBAL\Platforms\AbstractPlatform $platform)
    {
        if ($value === null) {
            return $value;
        }
        if ($value instanceof \DateTimeInterface || $value instanceof \WP_Ultimo\Dependencies\Carbon\CarbonInterface) {
            return $value->format('Y-m-d H:i:s.u');
        }
        throw \WP_Ultimo\Dependencies\Doctrine\DBAL\Types\ConversionException::conversionFailedInvalidType($value, $this->getName(), ['null', 'DateTime', 'Carbon']);
    }
}
