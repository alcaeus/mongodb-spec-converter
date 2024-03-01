<?php

namespace App\Converter\Crud\V1;

use App\Converter\InitialDataConverter;
use App\Converter\ListConverter;
use App\Converter\RootConverterInterface;
use App\Converter\RunOnRequirementConverter;
use App\Converter\TestSuiteConverterInterface;

final class CrudV1SuiteConverter implements TestSuiteConverterInterface
{
    public static function getInputDir(): string
    {
        return __DIR__ . '/../../../../spec/source/crud/tests/v1/*/';
    }

    public static function getOutputDir(): string
    {
        return __DIR__ . '/../../../../spec/source/crud/tests/v1-unified/';
    }

    public static function getMask(): string
    {
        return '*.yml';
    }

    public static function getRootConverter(): ?RootConverterInterface
    {
        return new CrudV1RootConverter();
    }

    public static function getItemConverters(): array
    {
        return [
            'data' => new InitialDataConverter(),
            'tests' => new ListConverter(new CrudV1TestConverter(), true),
        ];
    }
}
