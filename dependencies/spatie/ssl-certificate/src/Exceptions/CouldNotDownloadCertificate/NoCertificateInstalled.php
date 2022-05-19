<?php

namespace WP_Ultimo\Dependencies\Spatie\SslCertificate\Exceptions\CouldNotDownloadCertificate;

use WP_Ultimo\Dependencies\Spatie\SslCertificate\Exceptions\CouldNotDownloadCertificate;
class NoCertificateInstalled extends CouldNotDownloadCertificate
{
    public function __construct(string $hostName)
    {
        parent::__construct("Could not find a certificate on  host named `{$hostName}`.");
    }
}
