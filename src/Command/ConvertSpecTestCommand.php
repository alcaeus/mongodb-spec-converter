<?php

namespace App\Command;

use App\Converter\Crud\V1\CrudV1SuiteConverter;
use App\Converter\Crud\V2\CrudV2SuiteConverter;
use App\Converter\SpecTestToUnifiedConverter;
use App\Converter\TestSuiteConverterInterface;
use App\Converter\RetryableWrites\RetryableWritesSuiteConverter;
use App\Converter\Transactions\TransactionsSuiteConverter;
use LogicException;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\InvalidArgumentException as InvalidConsoleArgumentException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use function is_subclass_of;
use function sprintf;

#[AsCommand('app:convert-spec-tests')]
class ConvertSpecTestCommand extends Command
{
    private const SUITES = [
        'crud-v1' => CrudV1SuiteConverter::class,
        'crud-v2' => CrudV2SuiteConverter::class,
        'retryable-writes' => RetryableWritesSuiteConverter::class,
        'transactions' => TransactionsSuiteConverter::class,
    ];

    protected function configure()
    {
        $this->checkSuites();

        $this
            ->addArgument('suite', InputArgument::REQUIRED, 'Test suite to convert')
            ->addArgument('mask', InputArgument::OPTIONAL, 'Optional file mask for tests')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $suite = $input->getArgument('suite');

        if (!isset(self::SUITES[$suite])) {
            throw new InvalidConsoleArgumentException(sprintf('No converter found for test suite "%s".', $suite));
        }

        $converter = self::SUITES[$suite];

        (new SpecTestToUnifiedConverter($converter))->convert($input->getArgument('mask'));

        return Command::SUCCESS;
    }

    private function checkSuites(): void
    {
        foreach (self::SUITES as $suite => $class) {
            if (is_subclass_of($class, TestSuiteConverterInterface::class)) {
                continue;
            }

            throw new LogicException(sprintf('Converter for suite "%s" does not implement the correct interface.', $suite));
        }
    }
}
