<?php

/**
 * Thanks to https://github.com/flaushi for his suggestion:
 * https://github.com/doctrine/dbal/issues/2873#issuecomment-534956358
 */
namespace WP_Ultimo\Dependencies\Carbon\Doctrine;

use WP_Ultimo\Dependencies\Doctrine\DBAL\Platforms\AbstractPlatform;
class CarbonType extends \WP_Ultimo\Dependencies\Carbon\Doctrine\DateTimeType implements \WP_Ultimo\Dependencies\Carbon\Doctrine\CarbonDoctrineType
{
    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'carbon';
    }
    /**
     * {@inheritdoc}
     */
    public function requiresSQLCommentHint(\WP_Ultimo\Dependencies\Doctrine\DBAL\Platforms\AbstractPlatform $platform)
    {
        return \true;
    }
}
