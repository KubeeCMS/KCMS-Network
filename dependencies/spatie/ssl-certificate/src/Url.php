<?php

namespace WP_Ultimo\Dependencies\Spatie\SslCertificate;

use WP_Ultimo\Dependencies\Spatie\SslCertificate\Exceptions\InvalidUrl;
class Url
{
    /** @var string */
    protected $url;
    /** @var array */
    protected $parsedUrl;
    public function __construct(string $url)
    {
        if (!starts_with($url, ['http://', 'https://', 'ssl://'])) {
            $url = "https://{$url}";
        }
        if (\function_exists('idn_to_ascii') && \strlen($url) < 61) {
            $url = \idn_to_ascii($url, \false, \INTL_IDNA_VARIANT_UTS46);
        }
        if (!\filter_var($url, \FILTER_VALIDATE_URL)) {
            throw \WP_Ultimo\Dependencies\Spatie\SslCertificate\Exceptions\InvalidUrl::couldNotValidate($url);
        }
        $this->url = $url;
        $this->parsedUrl = \parse_url($url);
        if (!isset($this->parsedUrl['host'])) {
            throw \WP_Ultimo\Dependencies\Spatie\SslCertificate\Exceptions\InvalidUrl::couldNotDetermineHost($this->url);
        }
    }
    public function getHostName() : string
    {
        return $this->parsedUrl['host'];
    }
    public function getPort() : int
    {
        return $this->parsedUrl['port'] ?? 443;
    }
}
