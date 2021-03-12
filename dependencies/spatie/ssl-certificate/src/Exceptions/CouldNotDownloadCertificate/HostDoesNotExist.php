<?php

namespace WP_Ultimo\Dependencies\Spatie\SslCertificate\Exceptions\CouldNotDownloadCertificate;

use WP_Ultimo\Dependencies\Spatie\SslCertificate\Exceptions\CouldNotDownloadCertificate;
class HostDoesNotExist extends \WP_Ultimo\Dependencies\Spatie\SslCertificate\Exceptions\CouldNotDownloadCertificate
{
    public function __construct(string $hostName)
    {
        parent::__construct("The host named `{$hostName}` does not exist.");
    }
}
