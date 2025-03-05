<?php

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Doctrine\DBAL\Connection;
use Exception;

// You can run the command from the command line like this
// php bin/console sync-microsoft-users
#[AsCommand(
    name: 'sync-microsoft-users',
    description: 'Sync Microsoft users with the local database',
)]
class SyncMicrosoftUsersCommand extends Command
{
    private $_connection;

    public function __construct(Connection $cn)
    {
        $this->_connection = $cn;
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('arg1', InputArgument::OPTIONAL, 'Argument description')
            ->addOption('option1', null, InputOption::VALUE_NONE, 'Option description')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        
        try {
            $this->_downloadIntoLocalDb();
        } catch(Exception $e) {
            $io->error($e);
            return Command::FAILURE;
        }

        $io->success('Synchronisation complete.');

        return Command::SUCCESS;
    }

    private function _downloadIntoLocalDb()
    {
        $url = 'https://graph.microsoft.com/v1.0/users';
        $limit = 600; // Fetch all users at once
        $fullUrl = $url . "?\$top=$limit";

        $client = new  \GuzzleHttp\Client();
        $response = $client->request('GET', $fullUrl, [
            'headers' => [
                'Authorization' => 'Bearer ' . $_ENV["Microsoft_JWT_TOKEN"],
                'ConsistencyLevel' => 'eventual'
            ]
        ]);

        $responseBody = json_decode($response->getBody());
        $users = $responseBody->value;

        foreach ($users as $user) {
            $dataset = ["uuid" => $user->id, "displayName" => $user->displayName, "givenName" => $user->givenName, "jobTitle" => $user->jobTitle, "mail" => $user->mail, "mobilePhone" => $user->mobilePhone, "surname" => $user->surname];
            $columns = [];
            $values = [];

            foreach ($dataset as $key => $value) {
                $columns[] = is_string($key) ? $key : json_encode($key);
                $values[] = json_encode($value, JSON_UNESCAPED_UNICODE);
            }
            
            $columnStr = join(",", $columns);
            $valueStr = join(",", $values);

            // Insert User into Database
            $sql = "REPLACE INTO microsoft_users ($columnStr) VALUES ($valueStr);";
            $this->_connection->prepare($sql)->executeQuery();
        }        
    }
}
