<?php

// File generated from our OpenAPI spec
namespace WP_Ultimo\Dependencies\Stripe\Service;

class EphemeralKeyService extends \WP_Ultimo\Dependencies\Stripe\Service\AbstractService
{
    /**
     * Invalidates a short-lived API key for a given resource.
     *
     * @param string $id
     * @param null|array $params
     * @param null|array|\Stripe\Util\RequestOptions $opts
     *
     * @throws \Stripe\Exception\ApiErrorException if the request fails
     *
     * @return \Stripe\EphemeralKey
     */
    public function delete($id, $params = null, $opts = null)
    {
        return $this->request('delete', $this->buildPath('/v1/ephemeral_keys/%s', $id), $params, $opts);
    }
    /**
     * Creates a short-lived API key for a given resource.
     *
     * @param null|array $params
     * @param null|array|\Stripe\Util\RequestOptions $opts
     *
     * @throws \Stripe\Exception\ApiErrorException if the request fails
     *
     * @return \Stripe\EphemeralKey
     */
    public function create($params = null, $opts = null)
    {
        if (!$opts || !isset($opts['stripe_version'])) {
            throw new \WP_Ultimo\Dependencies\Stripe\Exception\InvalidArgumentException('stripe_version must be specified to create an ephemeral key');
        }
        return $this->request('post', '/v1/ephemeral_keys', $params, $opts);
    }
}
