<?php

require __DIR__ . '/vendor/autoload.php';

$env = Dotenv\Dotenv::create(__DIR__);
$env->load();

define('ROOT_DIR', __DIR__);
define('STORAGE_DIR', ROOT_DIR . '/storage');

date_default_timezone_set(getenv('TIMEZONE'));

class ClientTest
{
    protected $client;
    public function __construct($client)
    {
        $this->client = $client;
        while ($line = fgetc($client)) {
            if (ltrim($line, "\r") === "\n") {
                break;
            }
        }
    }

    public function run()
    {
        $v = 'Hello World!';
        fwrite(
            $this->client,
            "HTTP/1.1 200 OK\n" .
            "Content-Length: " . strlen($v) . "\n" .
            "\n" .
            $v
        );
        fclose($this->client);
    }
}

class Test
{
    public function writeTLSContext(&$context)
    {
        stream_context_set_option($context, 'ssl', 'local_cert', '/var/src/ssl/privkey.pem');
        stream_context_set_option($context, 'ssl', 'local_pk', '/var/src/ssl/key.key');
        stream_context_set_option($context, 'ssl', 'allow_self_signed', true);
        stream_context_set_option($context, 'ssl', 'verify_peer', false);
        stream_context_set_option($context, 'ssl', 'verify_peer_name', false);
    }

    public function run()
    {
        \Swoole\Runtime::enableCoroutine();
        go(function () {
            $context = stream_context_create();
            $this->writeTLSContext($context);
            $server = stream_socket_server(
                sprintf(
                    'ssl://%s:%d',
                    '0.0.0.0',
                    20000,
                ),
                $errno,
                $errstr,
                STREAM_SERVER_BIND | STREAM_SERVER_LISTEN,
                $context
            );

            echo "Server is running\n";
            while (true) {
                while ($client = stream_socket_accept($server)) {
                    $this->writeTLSContext($context);
                    stream_socket_enable_crypto(
                        $client,
                        true,
                        STREAM_CRYPTO_METHOD_TLSv1_2_SERVER
                    );

                    echo 'Connected: 1234' . "\n";
                    go([new ClientTest($client), 'run']);
                }
            }
        });

        \Swoole\Event::wait();
    }
}

(new Test())->run();