<?php

/**     _  ___ _ _ ____        ____  
 *     | |/ (_) | |  _ \__   _|  _ \ 
 *     | ' /| | | | |_) \ \ / / |_) |
 *     | . \| | | |  __/ \ V /|  __/ 
 *     |_|\_\_|_|_|_|     \_/ |_|    
 */ 
   

namespace soradore\KillPvP;

use pocketmine\scheduler\Task;

class CallbackTask extends Task {

	protected $callable, $args;

	/**
	 * @param callable $callable
	 * @param array    $args
	 */
	public function __construct(callable $callable, array $args = [])
	{
		$this->callable = $callable;
		$this->args = $args;
		$this->args[] = $this;
	}

	/**
	 * @return callable
	 */
	public function getCallable() : callable
	{
		return $this->callable;
	}

	/**
	 * @param $currentTick
	 */
	public function onRun($currentTick)
	{
		call_user_func_array($this->callable, $this->args);//関数呼び出し
	}
}