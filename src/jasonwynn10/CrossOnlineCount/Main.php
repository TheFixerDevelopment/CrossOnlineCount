<?php
namespace jasonwynn10\CrossOnlineCount;

use jasonwynn10\CrossOnlineCount\libs\MCPEQuery;
use pocketmine\event\Listener;
use pocketmine\nbt\tag\StringTag;
use pocketmine\plugin\PluginBase;

use slapper\events\SlapperCreationEvent;
use slapper\events\SlapperDeletionEvent;


class Main extends PluginBase implements Listener {
	/** @var string[] $arr */
	private $arr = [];

	public function onEnable() {
		foreach($this->getServer()->getLevels() as $level) {
			if(!$level->isClosed()) {
				foreach($level->getEntities() as $entity) {
					if(isset($entity->namedtag->server)) {
						/** @var string $ip */
						$ip = $entity->namedtag->server->getValue();
						$this->arr[$entity->getId()] = $ip;
					}
				}
			}
		}
		$this->getServer()->getScheduler()->scheduleRepeatingTask(new UpdateTask($this), 5); // update tags every 5 ticks
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
	}

	public function onDisable() {
		foreach($this->arr as $eid => $ip) {
			$entity = $this->getServer()->findEntity($eid);
			if(isset($entity->namedtag->server)) {
				$lines = explode("\n", $entity->getNameTag());
				$lines[0] = $entity->namedtag->server->getValue();
				$nametag = implode("\n", $lines);
				$entity->setNameTag($nametag);
			}
		}
	}

	/**
	 * @priority LOW
	 *
	 * @param SlapperCreationEvent $ev
	 */
	public function onSlapperCreate(SlapperCreationEvent $ev) {
		$entity = $ev->getEntity();
		$lines = explode("\n", $entity->getNameTag());
		if(preg_match("/(\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}):(\d{1,5})/", $lines[0], $matches) == 1) {
			if(isset($matches[0])) {
				$entity->namedtag->server = new StringTag("server", $lines[0]);
				$this->arr[$entity->getId()] = $lines[0];
				$this->update();
			}else{
				$this->getLogger()->debug("regex failed");
			}
		}
	}

	/**
	 * @priority LOW
	 *
	 * @param SlapperDeletionEvent $ev
	 */
	public function onSlapperDelete(SlapperDeletionEvent $ev) {
		$entity = $ev->getEntity();
		if(isset($this->arr[$entity->getId()])) {
			unset($this->arr[$entity->getId()]);
		}
		if(isset($entity->namedtag->server)) {
			unset($entity->namedtag->server);
		}
		$this->update();
	}

	/**
	 * @api
	 */
	public function update() {
		foreach($this->arr as $eid => $ip) {
			$entity = $this->getServer()->findEntity($eid);
			if(empty($ip)) {
				unset($this->arr[$eid]);
				unset($entity->namedtag->server);
				continue;
			}
			$server = explode(":", $ip);

			$queryData = MCPEQuery::query($server[0], $server[1]);
			if(isset($queryData['error'])) {
				$this->getLogger()->error("Query Failed!");
				$this->getLogger()->error($queryData['error']);
				return;
			}
			$online = (int) $queryData['num'];

			$lines = explode("\n", $entity->getNameTag());
			$lines[0] = $online." Online";
			$nametag = implode("\n", $lines);

			$entity->setNameTag($nametag);
		}
	}
}