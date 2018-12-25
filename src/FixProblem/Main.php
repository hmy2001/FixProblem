<?php
declare(strict_types=1);

namespace FixProblem;

use pocketmine\block\IronDoor;
use pocketmine\block\IronTrapdoor;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\network\mcpe\protocol\LoginPacket;
use pocketmine\network\mcpe\protocol\ProtocolInfo;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;

class Main extends PluginBase implements Listener{
	private $inputData = [], $checkRightClickBlock = [];

	const INPUT_MODE_KEYBOARD_MOUSE = 1;
	const INPUT_MODE_TOUCH = 2;
	const INPUT_MODE_CONTROLLER = 3;

	public function onEnable(){
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
	}

	public function onPlayerInteract(PlayerInteractEvent $event){
		$block = $event->getBlock();
		$player = $event->getPlayer();
		switch($event->getAction()){
			case PlayerInteractEvent::RIGHT_CLICK_BLOCK:
				if($this->inputData[$player->getName()] !== self::INPUT_MODE_TOUCH){
					if(isset($this->checkRightClickBlock[$player->getName()])){
						if($this->checkRightClickBlock[$player->getName()] > microtime(true)){
							$this->checkRightClickBlock[$player->getName()] = microtime(true) + 0.1;

							$event->setCancelled(true);
							return;
						}else{
							unset($this->checkRightClickBlock[$player->getName()]);
						}
					}

					if(!isset($this->checkRightClickBlock[$player->getName()])){
						$this->checkRightClickBlock[$player->getName()] = microtime(true) + 0.1;
					}
				}

				if($player->getGamemode() !== Player::CREATIVE){
					if($block instanceof  IronDoor or $block instanceof IronTrapdoor){
						$event->setCancelled(true);
					}
				}
			break;
			case PlayerInteractEvent::RIGHT_CLICK_AIR://TODO: Not Implemented yet
			break;
		}
	}

	public function onDataPacketReceive(DataPacketReceiveEvent $event){
		$packet = $event->getPacket();
		if($packet::NETWORK_ID === ProtocolInfo::LOGIN_PACKET){
			/** @var LoginPacket $packet */
			$this->inputData[$packet->username] = $packet->clientData["CurrentInputMode"];
		}
	}

}