<?php
namespace Magnolia\Traits;

use Magnolia\Contract\ClientInterface;
use Magnolia\Contract\ProcedureInterface;
use Magnolia\Enum\ProcedureKeys;
use Magnolia\Stream\Stream;
use Monolog\Logger;
use Swoole\Coroutine\Channel;

/**
 * @property-read ClientInterface|Stream $client
 * @property-read Logger $logger
 */
trait ProcedureManageable
{
    public function pushToProcedureStack(
        string $procedureTargetClass,
        string $key,
        string $callbackClass,
        ...$parameters
    ): void {
        $this->getRedis()->lPush(
            $key,
            serialize([$callbackClass, $parameters])
        );
    }

    public function proceedProcedure(string $key, ...$anyParameters): void
    {
        while (($pair = $this->getRedis()->rPop($key)) !== null) {
            [ $callbackClass, $userParameters ] = unserialize($pair);
            /**
             * @var ProcedureInterface $procedure
             */
            $procedure = new $callbackClass();
            $procedure->exec(...array_merge($userParameters, $anyParameters));
        }
    }
}
