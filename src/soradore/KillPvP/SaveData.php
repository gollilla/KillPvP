<?php  

/**     _  ___ _ _ ____        ____  
 *     | |/ (_) | |  _ \__   _|  _ \ 
 *     | ' /| | | | |_) \ \ / / |_) |
 *     | . \| | | |  __/ \ V /|  __/ 
 *     |_|\_\_|_|_|_|     \_/ |_|    
 */ 
   
namespace soradore\KillPvP;

use pocketmine\utils\Config;
use pocketmine\Server;
use pocketmine\level\Position;

define("FILE_NAME", "setting.yml");

class SaveData {


	public function __construct($dataFolder)
	{
		if(!file_exists($dataFolder)){
			mkdir($dataFolder, 0744, true);
		}
		$this->data = new Config($dataFolder.FILE_NAME, Config::YAML, 
		                         [
                                  "Max_Kill"=>30,
                                  "Block_For_Join"=>["x"=>0, "y"=>4, "z"=>0, "world"=>"world"],
                                  "RED_SPAWN"=>["x"=>0, "y"=>4, "z"=>0, "world"=>"world"],
                                  "BLUE_SPAWN"=>["x"=>0, "y"=>4, "z"=>0, "world"=>"world"],
                                  "SET_ARMOR"=>"on",
                                  "KILL_MONEY"=>0,
		                         ]);
	}

	public function save(){
		$this->data->save();
	}

	public function getMaxKill(){
		return $this->data->get("Max_Kill");
	}

	public function getBlockForJoin(){
		$data = $this->data;
		$blockInfo = $data->get("Block_For_Join");
		$level = Server::getInstance()->getLevelByName($blockInfo["world"]);
        if($level == null){
        	return false;
        }
        return new Position($blockInfo["x"], $blockInfo["y"], $blockInfo["z"], $level);
	}

    public function setMaxKill(int $val){
    	$this->data->set("Max_Kill", $val);
    	$this->save();
    }

    public function setJoinBlock($block){
    	$this->data->set("Block_For_Join", ["x"=>$block->x, "y"=>$block->y, "z"=>$block->z, "world"=>$block->level->getName()]);
    	$this->save();
    }

    public function setRedSpawn($pos){
    	$this->data->set("RED_SPAWN", ["x"=>$pos->x, "y"=>$pos->y, "z"=>$pos->z, "world"=>$pos->level->getName()]);
    	$this->save();
    }

    public function setBlueSpawn($pos){
    	$this->data->set("BLUE_SPAWN", ["x"=>$pos->x, "y"=>$pos->y, "z"=>$pos->z, "world"=>$pos->level->getName()]);
    	$this->save();
    }


    public function getRedSpawn(){
    	$data = $this->data->get("RED_SPAWN");
    	return new Position($data["x"], $data["y"], $data["z"], Server::getInstance()->getLevelByName($data["world"]));
    }

    public function getBlueSpawn(){
    	$data = $this->data->get("BLUE_SPAWN");
    	return new Position($data["x"], $data["y"], $data["z"], Server::getInstance()->getLevelByName($data["world"]));
    }

    public function setArmorSetting($val){
        $this->data->set("SET_ARMOR", $val);
        $this->data->save();
    }

    public function getArmorSetting(){
        return $this->data->get("SET_ARMOR");
    }

    public function getKillMoneyAmount(){
        return $this->data->get("KILL_MONEY");
    }

}