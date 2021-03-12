<?php

namespace WP_Ultimo\Dependencies\Spatie\SslCertificate\Exceptions;

use Exception;
use WP_Ultimo\Dependencies\Spatie\SslCertificate\Exceptions\CouldNotDownloadCertificate\HostDoesNotExist;
use WP_Ultimo\Dependencies\Spatie\SslCertificate\Exceptions\CouldNotDownloadCertificate\NoCertificateInstalled;
use WP_Ultimo\Dependencies\Spatie\SslCertificate\Exceptions\CouldNotDownloadCertificate\UnknownError;
class CouldNotDownloadCertificate extends \Exception
{
    public static function hostDoesNotExist(string $hostName) : self
    {
        return new \WP_Ultimo\Dependencies\Spatie\SslCertificate\Exceptions\CouldNotDownloadCertificate\HostDoesNotExist($hostName);
    }
    public static function noCertificateInstalled(string $hostName) : self
    {
        return new \WP_Ultimo\Dependencies\Spatie\SslCertificate\Exceptions\CouldNotDownloadCertificate\NoCertificateInstalled($hostName);
    }
    public static function unknownError(string $hostName, string $errorMessage) : self
    {
        return new \WP_Ultimo\Dependencies\Spatie\SslCertificate\Exceptions\CouldNotDownloadCertificate\UnknownError($hostName, $errorMessage);
    }
}
