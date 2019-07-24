<?php
namespace Magnolia\Traits;

trait SecureConnectionManageable
{
    private $allowSelfSign = true;
    private $verifyPeer = false;

    public function isEnabledTLS(): bool
    {
        return ((int) getenv('SSL_ENABLE')) === 1;
    }

    public function writeTLSContext(&$context): void
    {
        if (!$this->isEnabledTLS()) {
            return;
        }

        stream_context_set_option(
            $context,
            'ssl',
            'local_cert',
            getenv('SSL_CERTIFICATE_FILE')
        );

        stream_context_set_option(
            $context,
            'ssl',
            'local_pk',
            getenv('SSL_CERTIFICATE_KEY')
        );

        stream_context_set_option(
            $context,
            'ssl',
            'allow_self_signed',
            ((int) getenv('SSL_ALLOW_SELF_SIGN')) === 1
        );

        stream_context_set_option(
            $context,
            'ssl',
            'verify_peer',
            ((int) getenv('SSL_VERIFY_PEER')) === 1
        );

        stream_context_set_option(
            $context,
            'ssl',
            'verify_peer_name',
            ((int) getenv('SSL_VERIFY_PEER_NAME')) === 1
        );
    }
}