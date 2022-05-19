<?php

namespace WP_Ultimo\Dependencies\Spatie\SslCertificate\Exceptions\CouldNotDownloadCertificate;

use WP_Ultimo\Dependencies\Spatie\SslCertificate\Exceptions\CouldNotDownloadCertificate;
class UnknownError extends CouldNotDownloadCertificate
{
    public function __construct(string $hostName, string $errorMessage)
    {
        parent::__construct("Could not download certificate for host `{$hostName}` because {$errorMessage}");
    }
}
