<?php

namespace App\Converter\RetryableWrites;

use App\Converter\InitialDataConverter;
use App\Converter\ListConverter;
use App\Converter\RootConverterInterface;
use App\Converter\RunOnRequirementConverter;
use App\Converter\TestSuiteConverterInterface;

final class RetryableWritesSuiteConverter implements TestSuiteConverterInterface
{
    public static function getInputDir(): string
    {
        return __DIR__ . '/../../../spec/source/retryable-writes/tests/legacy';
    }

    public static function getOutputDir(): string
    {
        return __DIR__ . '/../../../spec/source/retryable-writes/tests/unified/';
    }

    public static function getMask(): string
    {
        return '*.yml';
    }

    public static function getRootConverter(): ?RootConverterInterface
    {
        return new RetryableWritesRootConverter();
    }

    public static function getItemConverters(): array
    {
        return [
            'runOn' => new RunOnRequirementConverter(),
            // collection_name handled in root converter
            // database_name handled in root converter
            'data' => new InitialDataConverter(),
            'tests' => new ListConverter(new RetryableWritesTestConverter(), true),
        ];
    }
}
