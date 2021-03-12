<?php

/**
 * Thanks to https://github.com/flaushi for his suggestion:
 * https://github.com/doctrine/dbal/issues/2873#issuecomment-534956358
 */
namespace WP_Ultimo\Dependencies\Carbon\Doctrine;

use WP_Ultimo\Dependencies\Doctrine\DBAL\Types\VarDateTimeType;
class DateTimeType extends \WP_Ultimo\Dependencies\Doctrine\DBAL\Types\VarDateTimeType implements \WP_Ultimo\Dependencies\Carbon\Doctrine\CarbonDoctrineType
{
    use CarbonTypeConverter;
}
