<?php

declare(strict_types=1);

namespace App\Command;

use App\Model\LoanProposal;
use App\Service\LoanFeeCalculator\Contracts\FeeCalculator;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:calculate-fee',
    description: 'Calculates fee for loan',
)]
class CalculateFeeCommand extends Command
{
    public function __construct(
        private readonly FeeCalculator $calculator,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('amount', InputArgument::REQUIRED, 'Loan amount');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $loanAmount = $input->getArgument('amount');

        if (!is_numeric($loanAmount)) {
            $io->note('Loan amount must be a number');
            return Command::FAILURE;
        }

        $proposal = new LoanProposal(floatval($loanAmount));
        $fee = $this->calculator->calculate($proposal);
        $io->success(sprintf('Fee: %s PLN', strval($fee)));

        return Command::SUCCESS;
    }
}
