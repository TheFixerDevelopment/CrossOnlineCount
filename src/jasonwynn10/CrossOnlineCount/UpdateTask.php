<?php
namespace jasonwynn10\CrossOnlineCount;

use pocketmine\scheduler\Task;

class UpdateTask extends Task {
	public function onRun($currentTick) {
		$this->getOwner()->update();
	}
}
