<?php
namespace jasonwynn10\CrossOnlineCount;

use jasonwynn10\CrossOnlineCount\libs\MinecraftQuery;
use pocketmine\plugin\Plugin;
use pocketmine\scheduler\PluginTask;

class UpdateTask extends PluginTask {
	/** @var array $arr */
	public $arr = [];
	/** @var MinecraftQuery $query */
	private $query;
	public function __construct(Plugin $owner, array $arr, MinecraftQuery $query) {
		parent::__construct($owner);
		$this->arr = $arr;
		$this->query = $query;
	}
	public function onRun(int $currentTick) {
		foreach($this->arr as $eid => $ip) {
			$server = explode(":", $ip);
			$this->query->Connect($server[0], $server[1]);
			if(isset($this->query->GetInfo()["numplayers"])) {
				$online = $this->query->GetInfo()["numplayers"];
			}
			if(isset($this->query->GetInfo()["maxplayers"])) {
				$maxplayers = $this->query->GetInfo()["maxplayers"];
			}

		}
	}
}