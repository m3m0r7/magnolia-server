<?php
namespace Magnolia\Contract;

use Magnolia\Stream\Stream;

interface ProcedureInterface
{
    public function exec(...$parameters): void;
}
