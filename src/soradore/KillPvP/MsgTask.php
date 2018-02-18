<?php

/**     _  ___ _ _ ____        ____  
 *     | |/ (_) | |  _ \__   _|  _ \ 
 *     | ' /| | | | |_) \ \ / / |_) |
 *     | . \| | | |  __/ \ V /|  __/ 
 *     |_|\_\_|_|_|_|     \_/ |_|    
 */ 
   

namespace soradore\KillPvP;

use pocketmine\scheduler\PluginTask;

class MsgTask extends PluginTask {

	
	public function __construct($main){
		parent::__construct($main);
		$this->main = $this->getOwner();
	}
	/**
	 * @param $currentTick
	 */
	public function onRun($currentTick){
		$main = $this->main;
		$space = str_repeat("  ", 20);
		$maxKill = $main->data->getMaxKill();
		$redKill = $main->getKillCount("RED");
		$blueKill = $main->getKillCount("BLUE");
		$remainRed = ($maxKill - $redKill);
		$remainBlue = ($maxKill - $blueKill);
		$this->main->broadcastTips("\n\n{$space}§6残りKILL数\n\n{$space}§cRED §f: §a{$remainRed} Kill\n{$space}§bBLUE §f: §a{$remainBlue} Kill");
	}
}