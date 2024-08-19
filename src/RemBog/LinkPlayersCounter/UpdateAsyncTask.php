<?php

namespace RemBog\LinkPlayersCounter;

use pocketmine\scheduler\AsyncTask;
use Exception;
use RemBog\LinkPlayersCounter\libpmquery\PMQuery;

class UpdateAsyncTask extends AsyncTask
{
    private string $linkedServers;

    public function __construct(array $linkedServers)
    {
        $this->linkedServers = json_encode($linkedServers);
    }

    public function onRun(): void
    {
        $linkedServers = json_decode($this->linkedServers);
        $playerCount = 0;
        foreach ($linkedServers as $server) {
            $serverInfos = explode(":", $server);
            $ip = $serverInfos[0];
            $port = $serverInfos[1];

            try {
                $queryData = PMQuery::query($ip, $port);
            }catch (Exception) {
                continue;
            }

            $playerCount += $queryData['Players'];
        }
        $this->setResult($playerCount);
    }

    public function onCompletion(): void
    {
        $result = $this->getResult();
        if($result >= 0) {
            LinkPlayersCounter::getInstance()->setLinkedServersPlayerCount($result);
        }
    }
}