<?php
// src/Command/UpdateWordlistCommand.php
namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use App\Service\WordListUpdater;

class UpdateWordlistCommand extends Command {
    private $updater;

    public function __construct(WordListUpdater $updater) {
        $this->updater = $updater;
        parent::__construct();
    }

    protected function configure() {
        $this->setName('app:update-wordlist')
            ->setDescription('Updates prohibited words list');
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        try {
            $added = $this->updater->fetchLatestList();
            $output->writeln("Added $added new prohibited terms");
            return Command::SUCCESS;
        } catch(\Exception $e) {
            $output->writeln("Error: ".$e->getMessage());
            return Command::FAILURE;
        }
    }
}