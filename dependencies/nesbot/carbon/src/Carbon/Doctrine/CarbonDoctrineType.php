<?php

/**
 * Thanks to https://github.com/flaushi for his suggestion:
 * https://github.com/doctrine/dbal/issues/2873#issuecomment-534956358
 */
namespace WP_Ultimo\Dependencies\Carbon\Doctrine;

use WP_Ultimo\Dependencies\Doctrine\DBAL\Platforms\AbstractPlatform;
interface CarbonDoctrineType
{
    public function getSQLDeclaration(array $fieldDeclaration, \WP_Ultimo\Dependencies\Doctrine\DBAL\Platforms\AbstractPlatform $platform);
    public function convertToPHPValue($value, \WP_Ultimo\Dependencies\Doctrine\DBAL\Platforms\AbstractPlatform $platform);
    public function convertToDatabaseValue($value, \WP_Ultimo\Dependencies\Doctrine\DBAL\Platforms\AbstractPlatform $platform);
}
