<?php

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;

class DataLoopCommand extends Command
{
    protected static $defaultName = 'app:data-loop';

    protected function configure()
    {
        $this->setDescription('Run a command every minute for a specified duration')
            ->addOption('duration', null, InputOption::VALUE_REQUIRED, 'Duration in seconds');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $duration = $input->getOption('duration');

        if (!is_numeric($duration) || $duration <= 0) {
            $output->writeln('<error>Invalid duration specified.</error>');
            return Command::FAILURE;
        }

        $startTime = time();
        $endTime = $startTime + $duration;

        while (time() < $endTime) {
            $output->writeln('Running your command...');

            // Run your actual command here
           // $this->getApplication()->run(new StringInput('php bin/console app:save-api-data'), $output);
            $process = new Process(['php', 'bin/console', 'app:save-api-data']);
            $process->run();

            sleep(60); // Sleep for one minute
        }

        $output->writeln('<info>Duration reached. Exiting...</info>');

        return Command::SUCCESS;
    }
}
