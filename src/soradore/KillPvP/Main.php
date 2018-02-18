<?php 


/**     _  ___ _ _ ____        ____  
 *     | |/ (_) | |  _ \__   _|  _ \ 
 *     | ' /| | | | |_) \ \ / / |_) |
 *     | . \| | | |  __/ \ V /|  __/ 
 *     |_|\_\_|_|_|_|     \_/ |_|    
 */ 


namespace soradore\KillPvP;

use pocketmine\Server;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;

use pocketmine\event\Listener;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityDeathEvent;
use pocketmine\Entity;


use pocketmine\command\Command;
use pocketmine\command\CommandSender;

class Main extends PluginBase implements Listener{

	/**
	 * @value SaveData $data
	 */
    
    public $data = null;
    public $team = null;
    public $msgTask = null;
    public $count = [];
    public $task = [];
    public $game_status = true;
    private $coinAPI = null;
    private $loadCoinApi = false;

    public function onEnable(){
    	$this->data = new SaveData($this->getDataFolder());
		$this->team = new TeamHolder($this->data);
		$this->msgTask = new MsgTask($this);
		$this->getServer()->getScheduler()->scheduleRepeatingTask($this->msgTask, 20);
        $this->getServer()->getPluginManager()->registerEvents($this, $this);

        //game setting
        $this->count["RED"] = $this->count["BLUE"] = 0;
        if($this->getServer()->getPluginManager()->getPlugin("CoinSystem") !== NULL){
        	$this->coinAPI = $this->getServer()->getPluginManager()->getPlugin("CoinSystem");
        	$this->loadCoinApi = true;
        }
    }


    public function onCommand(CommandSender $sender, Command $cmd, $label, array $args){
    	switch($cmd->getName()){
        	case 'setblock':
        		if($sender instanceof Player){
        			$name = $sender->getName();
        			$this->task[$name] = "setblock";
        			$sender->sendMessage("§6> 設定したいブロックをタッチしてください");
        		}
        		break;
        	case 'setpos':
        	    if($sender instanceof Player){
        	    	if(isset($args[0])){
        	    		switch($args[0]){
        	    			case 'red':
        	    				$this->data->setRedSpawn($sender);
        	    				$sender->sendMessage("§e> REDチームのスポーン地点を設定しました");
        	    				break;
        	    			case 'blue':
        	    				$this->data->setBlueSpawn($sender);
        	    				$sender->sendMessage("§e> BLUEチームのスポーン地点を設定しました");
        	    				break;
        	    		}
        	    	}
        	    }
        	    break;
        	case 'armor':
        	    if(isset($args[0])){
        	    	switch($args[0]){
        	    		case 'on':
        	    			$this->data->setArmorSetting("on");
        	    			$sender->sendMessage("§a> 防具配布をOnにしました");
        	    			break;
        	    		case 'off':
        	    			$this->data->setArmorSetting("off");
        	    			$sender->sendMessage("§e> 防具配布をOffにしました");
        	    			break;
        	    		default:
        	    		    $sender->sendMessage("§c>使い方 /armor [on/off]");
        	    	}
                }
        }
        return true;
    }


    public function addPlayer(Player $player){
		$team = $this->team;
		$red = $team->getRedPlayersCount();
		$blue = $team->getBluePlayersCount();
		if($red === $blue){
			$r = rand(1, 100);
			if($r % 2 == 0){
				$team->addRed($player);
			}else{
				$team->addBlue($player);
			}
		}else if($red < $blue){
			$team->addRed($player);
		}else{
			$team->addBlue($player);
		}
	}


	public function onTouch(PlayerInteractEvent $ev){
		$player = $ev->getPlayer();
		$name = $player->getName();
		$block = $ev->getBlock();
		if(isset($this->task[$name])){
			switch ($this->task[$name]) {
				case 'setblock':
					$this->data->setJoinBlock($block);
					$player->sendMessage("§e> 参加用ブロックを設定しました");
					unset($this->task[$name]);
					break;
			}
			return true;
		}
		if($this->isJoinBlock($block)){
			if($this->game_status){
				if(!$this->isPlayer($player)){
				    $this->addPlayer($player);
			    }
			}else{
				$player->sendMessage("§c> 現在終了しています。");
			}
		}
	}


	public function onDamage(EntityDamageEvent $ev){
		$entity = $ev->getEntity();
		if($ev instanceof EntityDamageByEntityEvent){
			$damager = $ev->getDamager();
			if($entity instanceof Player && $damager instanceof Player){
				$teamE = $this->getTeam($entity);
				$teamD = $this->getTeam($damager);
				if($this->isPlayer($entity) && $this->isPlayer($damager)){
					if($teamD == $teamE){
					    $ev->setCancelled();
					    $damager->sendPopup("§c> 同じチームは攻撃できないよ");
				    }
				}else{
					$ev->setCancelled();
				}
			}
		}
	}


	public function isJoinBlock($block){
    	$data = $this->data->getBlockForJoin();
    	$data = $data->level->getBlock($data);
        return ($data == $block);
    }


    public function isPlayer($player){
        return $this->team->isPlayer($player);
    }


    public function getTeam($player){
    	return $this->team->getTeam($player);
    }


    public function onDeath(PlayerDeathEvent $ev){
    	$entity = $ev->getPlayer();
    	$cause = $entity->getLastDamageCause();
    	if($cause instanceof EntityDamageByEntityEvent){
    		$damager = $cause->getDamager();
    		if($damager instanceof Player){
    			$teamD = $this->getTeam($damager);
    			$this->addKillCount($teamD);
    			$this->check($teamD);
    			if($this->loadCoinApi){
    				$money = $this->data->getKillMoneyAmount();
    				$dname = $damager->getName();
    				$ename = $entity->getName();
    				$this->coinAPI->increase($dname, $money);
    				$damager->sendMessage("§b> §6".$ename." §bを倒して§6 \$".$money."　§b手にいれました");
    			}
    			$ev->setKeepInventory(true);
    		}
    	}
    }


    public function addKillCount($team){
    	$this->count[$team] += 1;
    }


    public function getKillCount($team){
    	return $this->count[$team];
    }


    public function check($team){
    	$maxKill = $this->data->getMaxKill();
    	if($this->getKillCount($team) == $maxKill){
    		$this->endGame($team);
    	}
    }


    public function endGame($won){
    	$this->getServer()->broadcastMessage("§6終了!!");
    	$this->game_status = false;
    	$this->getServer()->getScheduler()->cancelTask($this->msgTask->getTaskId());
    	switch ($won) {
    		case 'RED':
    			$this->getServer()->broadcastMessage("§2------結果発表------");
    			$this->getServer()->broadcastMessage("§bREDチームの勝利");
    			break;
    		
    		case 'BLUE':
    			$this->getServer()->broadcastMessage("§2------結果発表------");
    			$this->getServer()->broadcastMessage("§bBLUEチームの勝利");
    			break;
    	}
    	$this->getServer()->broadcastMessage("10秒後に再起動します");
    	$this->getServer()->getScheduler()->scheduleDelayedTask(new CallbackTask([$this, "shutdown"]), 20 * 10);
    }


    public function onQuit(PlayerQuitEvent $ev){
    	$player = $ev->getPlayer();
    	if($this->isPlayer($player)){
    		$this->team->removeArmor($player);
    		$this->team->removePlayer($player);
    	}
    }


    public function broadcastTips($msg = ""){
        $players = $this->team->getAllPlayers();
        foreach ($players as $player) {
        	$player->sendTip($msg);
        }
    }


    public function shutdown(){
    	$this->getServer()->shutdown();
    }


}
