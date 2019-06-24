<?php
namespace Magnolia\Client;

use Magnolia\Contract\ClientInterface;
use Magnolia\Enum\KindEnv;
use Magnolia\Enum\RedisKeys;
use Magnolia\Utility\Functions;
use Monolog\Logger;

final class StreamingPipeline extends AbstractClient implements ClientInterface
{
    use \Magnolia\Traits\ClientManageable;
    use \Magnolia\Traits\HeaderReadable;
    
    protected $loggerChannelName = 'StreamingPipeline.Client';

    public function start(): void
    {
        if (!$this->proceedHeaders()) {
            return;
        }

        // Write headers section.
        $this->client
            ->writeLine("HTTP/1.1 200 OK")
            ->writeLine('Age: 0')
            ->writeLine('Cache-Control: no-cache, private')
            ->writeLine("Content-Type: multipart/x-mixed-replace; boundary=" . $this->client->getUUID())
            ->writeLine("");

        // make an image
        $image = imagecreatetruecolor(640, 480);
        imagefill($image, 0, 0, imagecolorallocate($image, 0, 0, 0));
        ob_start();
        imagejpeg($image);
        $image = ob_get_clean();

        // send
        $this->client
            ->writeLine('--' . $this->client->getUUID())
            ->writeLine('Content-Type: image/jpeg')
            ->writeLine('Content-Length: ' . strlen($image))
            ->writeLine('')
            ->write($image);
    }
}
