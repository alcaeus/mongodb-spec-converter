<?php

namespace App\Converter\Tests;

use App\Converter\ListConverter;
use App\Converter\YamlAnchorAwareConverter;
use Symfony\Component\Yaml\Reference\Reference;

final class ExpectationsConverter extends YamlAnchorAwareConverter
{
    protected function doConvert(string $fieldName, mixed $data): ?array
    {
        if ($data === null) {
            return null;
        }

        return [[
            'client' => new Reference('client0'),
            'events' => (new ListConverter(new ExpectationConverter(), false))
                ->convert('', $data),
        ]];
    }
}
