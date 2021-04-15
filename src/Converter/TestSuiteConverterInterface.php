<?php

namespace App\Converter;

interface TestSuiteConverterInterface
{
    public static function getInputDir(): string;

    public static function getOutputDir(): string;

    public static function getMask(): string;

    public static function getItemConverters(): array;
}
