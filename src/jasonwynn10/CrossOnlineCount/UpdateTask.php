<?php
namespace jasonwynn10\CrossOnlineCount;

use pocketmine\scheduler\PluginTask;

class UpdateTask extends PluginTask {
	public function onRun($currentTick) {
		$this->getOwner()->update();
	}
}
