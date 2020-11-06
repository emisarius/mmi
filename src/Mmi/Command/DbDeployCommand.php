<?php

namespace Mmi\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DbDeployCommand extends CommandAbstract
{

    public function configure()
    {
        $this->setDescription('Deploy database incremental');
        parent::configure();
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|null|void
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            (new \Mmi\Db\Deployer)->deploy();
        } catch (\Exception $e) {
            $output->writeln($e->getMessage());
            return 1;
        }
        return 0;
    }

}