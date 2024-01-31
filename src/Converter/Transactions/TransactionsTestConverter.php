<?php

namespace App\Converter\Transactions;

use App\Converter\ListConverter;
use App\Converter\Operation\LegacyOperationConverter;
use App\Converter\TestItemConverterInterface;
use App\Converter\Tests\ExpectationsConverter;
use App\Converter\Tests\OutcomeConverter;
use App\Converter\YamlAnchorAwareConverter;
use Symfony\Component\Yaml\Reference\Reference;
use function App\array_filter_null;
use function array_unshift;

final class TransactionsTestConverter implements TestItemConverterInterface
{
    public function convert(string $fieldName, mixed $data): mixed
    {
        if (isset($data->failPoint)) {
            array_unshift($data->operations, (object) [
                'name' => 'failPoint',
                'object' => 'testRunner',
                'arguments' => (object) [
                    'client' => new Reference('client0'),
                    'failPoint' => $data->failPoint,
                ],
            ]);
        }

        $operations = (new ListConverter(new LegacyOperationConverter(), false))
            ->convert('', $data->operations);

        $expectations = (new ExpectationsConverter())
            ->convert('', $data->expectations ?? null);

        $outcome = (new OutcomeConverter())
            ->convert('', $data->outcome ?? null);

        return array_filter_null([
            'description' => $data->description,
            'skipReason' => $data->skipReason ?? null,
            // useMultipleMongoses not supported, will cause errors to point out manual work
            'useMultipleMongoses' => $data->useMultipleMongoses ?? null,
            // failPoint handled above
            // clientOptions not supported, will cause errors to point out manual work
            'clientOptions' => $data->clientOptions ?? null,
            // clientOptions not supported, will cause errors to point out manual work
            'sessionOptions' => $data->sessionOptions ?? null,
            'operations' => $operations,
            'expectEvents' => $expectations,
            'outcome' => $outcome,
        ]);
    }
}