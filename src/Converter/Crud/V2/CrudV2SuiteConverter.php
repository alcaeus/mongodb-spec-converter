<?php

namespace App\Converter\Crud\V2;

use App\Converter\Crud\ListConverter;
use App\Converter\InitialDataConverter;
use App\Converter\RootConverterInterface;
use App\Converter\RunOnRequirementConverter;
use App\Converter\TestSuiteConverterInterface;

final class CrudV2SuiteConverter implements TestSuiteConverterInterface
{
    public static function getInputDir(): string
    {
        return __DIR__ . '/../../../../spec/source/crud/tests/v2';
    }

    public static function getOutputDir(): string
    {
        return __DIR__ . '/../../../../spec/source/crud/tests/unified/';
    }

    public static function getMask(): string
    {
        return '*.yml';
    }

    public static function getManuallyModifiedFiles(): array
    {
        return [
            'aggregate-merge.yml',
            'aggregate-out-readConcern.yml',
            'bulkWrite-arrayFilters.yml',
            'bulkWrite-update-hint.yml',
            'bulkWrite-update-validation.yml',
            'find-allowdiskuse.yml',
            'replaceOne-hint.yml',
            'replaceOne-validation.yml',
            'unacknowledged-bulkWrite-delete-hint-clientError.yml',
            'unacknowledged-bulkWrite-update-hint-clientError.yml',
            'unacknowledged-deleteMany-hint-clientError.yml',
            'unacknowledged-deleteOne-hint-clientError.yml',
            'unacknowledged-findOneAndDelete-hint-clientError.yml',
            'unacknowledged-findOneAndReplace-hint-clientError.yml',
            'unacknowledged-findOneAndUpdate-hint-clientError.yml',
            'unacknowledged-replaceOne-hint-clientError.yml',
            'unacknowledged-updateMany-hint-clientError.yml',
            'unacknowledged-updateOne-hint-clientError.yml',
            'updateMany-hint.yml',
            'updateOne-hint.yml',
            'updateWithPipelines.yml',
        ];
    }

    public static function getRootConverter(): ?RootConverterInterface
    {
        return new CrudV2RootConverter();
    }

    public static function getItemConverters(): array
    {
        return [
            'runOn' => new RunOnRequirementConverter(),
            // collection_name handled in root converter
            // database_name handled in root converter
            'data' => new InitialDataConverter(),
            'tests' => new ListConverter(new CrudV2TestsConverter()),
        ];
    }
}
