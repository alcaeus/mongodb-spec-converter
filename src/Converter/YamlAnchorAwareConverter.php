<?php

namespace App\Converter;

use Symfony\Component\Yaml\Reference\Anchor;
use Symfony\Component\Yaml\Reference\Reference;

abstract class YamlAnchorAwareConverter implements TestItemConverterInterface
{
    final public function convert(string $fieldName, mixed $data): mixed
    {
        if ($data instanceof Reference) {
            return $data;
        }

        if ($data instanceof Anchor) {
            $anchorName = $data->getName();
            $data = $data->getValue();
        }

        $result = $this->doConvert($fieldName, $data);

        return isset($anchorName) ? new Anchor($anchorName, $result) : $result;
    }

    abstract protected function doConvert(string $fieldName, mixed $data): mixed;
}
