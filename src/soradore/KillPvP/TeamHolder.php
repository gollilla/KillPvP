<?php 

/**     _  ___ _ _ ____        ____  
 *     | |/ (_) | |  _ \__   _|  _ \ 
 *     | ' /| | | | |_) \ \ / / |_) |
 *     | . \| | | |  __/ \ V /|  __/ 
 *     |_|\_\_|_|_|_|     \_/ |_|    
 */ 
                                       
namespace soradore\KillPvP;

use pocketmine\Server;
use pocketmine\item\Item;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\IntTag;

class TeamHolder{

	public function __construct($data){
		$this->data = $data;
        $this->red = [];
        $this->blue = [];
	}

	public function addRed($player){
        $this->red[] = $player;
        $player->sendMessage("§cRedチームに参加しました");
        $player->setSpawn($this->data->getRedSpawn());
        $player->setNameTag("§cRED §f| §6".$player->getName());
        $player->setDisplayName("§cRED §f| §6".$player->getName()."§f");
        $player->teleport($this->data->getRedSpawn());
        $this->setArmor($player);
	}

	public function addBlue($player){
		$this->blue[] = $player;
		$player->sendMessage("§bBlueチームに参加しました");
		$player->setSpawn($this->data->getBlueSpawn());
		$player->setNameTag("§bBLUE §f| §6".$player->getName());
		$player->setDisplayName("§bBLUE §f| §6".$player->getName()."§f");
		$player->teleport($this->data->getBlueSpawn());
		$this->setArmor($player);
	}

	public function isPlayer($player){
		$array = array_merge($this->red, $this->blue);
		return in_array($player, $array, true);
	}

	public function getRedPlayersCount(){
		return count($this->red);
	}

	public function getBluePlayersCount(){
		return count($this->blue);
	}

	public function removePlayer($player){
		$team = $this->getTeam($player);
		switch ($team) {
			case 'RED':
				$key = array_search($player, $this->red, true);
				unset($this->red[$key]);
				array_values($this->red);
				break;
			
			case "BLUE":
				$key = array_search($player, $this->blue, true);
				unset($this->blue[$key]);
				array_values($this->blue);
				break;
		}
		$player->setNameTag($player->getName());
		$player->setSpawn(Server::getInstance()->getDefaultLevel()->getSafeSpawn());
	}

	public function getTeam($player){
		if($this->isPlayer($player)){
			if(in_array($player, $this->red, true)){
				return "RED";
			}else{
				return "BLUE";
			}
		}
	}

	public function getTeamColor($team){
        switch ($team) {
            case 'RED':
                return 0xFF0000;
                break;
            
            case 'BLUE':
                return 0x0000FF;
                break;
        }
        return false;
    }

	public function getAllPlayers(){
		return array_merge($this->red, $this->blue);
	}

	public function getArmor($team){
        $color = $this->getTeamColor($team);
        $tempTag = new CompoundTag("", []);
        $tempTag->customColor = new IntTag("customColor", $color);
        $items = [
                  Item::get(298, 0, 1)->setCompoundTag($tempTag),
                  Item::get(299, 0, 1)->setCompoundTag($tempTag),
                  Item::get(300, 0, 1)->setCompoundTag($tempTag),
                  Item::get(301, 0, 1)->setCompoundTag($tempTag)
                 ];
        return $items;
    }

    public function setArmor($player){
    	switch($this->data->getArmorSetting()){
    		case "on":
    		case "true":
    		case 1:
    		    $team = $this->getTeam($player);
    	        $armor = $this->getArmor($team);
    	        $inventory = $player->getInventory();
    	        for($i=0;$i<4;$i++){
    		        $inventory->setArmorItem($i, $armor[$i]);
    	        }
    	        $player->getInventory()->sendArmorContents($player);  
            break;
    	}
    }

    public function removeArmor($player){
    	$inventory = $player->getInventory();
    	$items = [
                  Item::get(298, 0, 0),
                  Item::get(299, 0, 0),
                  Item::get(300, 0, 0),
                  Item::get(301, 0, 0)
                 ];
        $inventory = $player->getInventory();
    	for($i=0;$i<4;$i++){
    		$inventory->setArmorItem($i, $items[$i]);
    	}
    	$player->getInventory()->sendArmorContents($player); 
    }
}