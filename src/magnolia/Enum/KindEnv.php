<?php
namespace Magnolia\Enum;

final class KindEnv implements EnumInterface
{
    const KIND_TEMPERATURE = 0x00;
    const KIND_HUMIDITY = 0x10;
    const KIND_PRESSURE = 0x20;
    const KIND_CPU_TEMPERATURE = 0x30;
    const KIND_READ_STARTING = 0xff;
}