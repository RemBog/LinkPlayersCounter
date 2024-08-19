<?php

namespace RemBog\LinkPlayersCounter;

use pocketmine\event\Listener;
use pocketmine\event\server\QueryRegenerateEvent;
use pocketmine\plugin\PluginBase;
use pocketmine\scheduler\ClosureTask;
use pocketmine\utils\SingletonTrait;

class LinkPlayersCounter extends PluginBase implements Listener
{
    use SingletonTrait;

    /** @var int $linkedServersPlayerCount */
    private int $linkedServersPlayerCount;

    protected function onEnable(): void
    {
        $this::setInstance($this);

        $config = $this->getConfig();
        $updateTime = $config->get("update-time", 30);
        $linkedServers = $config->get("linked-servers", []);

        if(empty($linkedServers)) {
            $this->getServer()->getLogger()->info("[LinkPlayersCounter] §6Plugin deactivated because no server has been added to the configuration.");
        }else{
            $info = "[LinkPlayersCounter] §6The number of players has been successfully linked with the following server(s) :§7";
            foreach ($linkedServers as $server) {
                $info .= " $server,";
            }
            $info = substr($info, 0, -1);
            $this->getServer()->getLogger()->info($info);

            $this->getServer()->getPluginManager()->registerEvents($this, $this);

            $this->getScheduler()->scheduleRepeatingTask(new ClosureTask(function () use ($linkedServers): void {
                $this->getServer()->getAsyncPool()->submitTask(new UpdateAsyncTask($linkedServers));
            }), 20 * $updateTime);
        }
    }

    public function setLinkedServersPlayerCount(int $count): void
    {
        $this->linkedServersPlayerCount = $count;
    }

    public function getLinkedServersPlayerCount(): int
    {
        return $this->linkedServersPlayerCount;
    }

    public function getTotalPlayerCount(): int
    {
        return count($this->getServer()->getOnlinePlayers()) + $this->getLinkedServersPlayerCount();
    }

    public function queryRegenerate(QueryRegenerateEvent $event): void
    {
        $event->getQueryInfo()->setPlayerCount($this->getTotalPlayerCount());
    }
}