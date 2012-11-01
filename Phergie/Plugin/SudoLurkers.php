<?php

class Phergie_Plugin_SudoLurkers extends Phergie_Plugin_SudoAbstract {

	protected $joinTime;
	protected $activity = array();
	protected $maxIdleSeconds = 1800;

	public function onLoad() {
		$this->getPluginHandler()->getPlugin('Command');
		$this->joinTime = time();

		$this->persistence = $this->config['sudolurkers.persistence'];

		if($this->persistence) {
			$this->restoreState('activity');
		}
	}

	/**
	 * Log activity for nick
	 * @param $nick
	 */
	protected function logActivity($nick) {
		$this->activity[trim($nick)] = time();
		$this->persistState('activity');
	}

	/**
	 * Remove activity for nick
	 * @param $nick
	 */
	protected function removeActivity($nick) {
		unset($this->activity[trim($nick)]);
		$this->persistState('activity');
	}

	/**
	 * Move activity from one nick to another
	 * @param $oldNick
	 * @param $newNick
	 */
	protected function moveActivity($oldNick, $newNick) {
		$this->activity[trim($oldNick)] = $this->activity[trim($newNick)];
		$this->removeActivity($oldNick);
		$this->persistState('activity');
	}

	/**
	 * When user messages channel or bot, log activity
	 */
	public function onPrivmsg() {
		$this->logActivity($this->getEvent()->getNick());
	}

	/**
	 * When user joins channel, log activity
	 */
	public function onJoin() {
		$this->logActivity($this->getEvent()->getNick());
	}

	/**
	 * When user leaves channel, remove activity
	 */
	public function onPart() {
		$this->removeActivity($this->getEvent()->getNick());
	}

	/**
	 * When user quits server, remove activity
	 */
	public function onQuit() {
		$this->removeActivity($this->getEvent()->getNick());
	}

	/**
	 * When user changes nick, move activity from old user to new user
	 */
	public function onNick() {
		$this->moveActivity($this->getEvent()->getNick(), $this->getEvent()->getArgument(0));
	}

	/**
	 * Populate activity array when bot joins channel
	 */
	public function onResponse() {
		if ($this->getEvent()->getCode() != Phergie_Event_Response::RPL_NAMREPLY) {
			return;
		}

		$descArray = explode(' ', $this->getEvent()->getDescription());
		$channel  = strtolower($descArray[1]);

		for($i = 3; $i < sizeof($descArray); $i++) {
			if(empty($descArray[$i])) {
				continue;
			}
			$this->logActivity($descArray[$i]);
		}
	}

	/**
	 * Command to list lurkers
	 */
	public function onCommandLurkers() {
		$numberOfUsers = sizeof($this->activity);
		$numberOfLurkers = 0;
		$currentTime = time();

		$lurkerList = "";
		foreach($this->activity as $activityNick => $activityTime) {
			$userIdleTime = $currentTime - $activityTime;
			if($userIdleTime >= $this->maxIdleSeconds) {
				$lurkerList .= $lurkerList != "" ? "," : "";
				$lurkerList .= "{$activityNick} ({$userIdleTime} seconds)";
				$numberOfLurkers++;
			}
		}

		$usersMessage = "There is one user in this channel, ";
		if($numberOfUsers > 1) {
			$usersMessage = "There are {$numberOfUsers} users in this channel, ";
		}

		$lurkersMessage = "";
		if($numberOfLurkers == 0) {
			$lurkersMessage = "and none are lurking.";
		} elseif($numberOfLurkers == 1) {
			$lurkersMessage = "and 1 is lurking: ";
		} else {
			$lurkersMessage = "and {$numberOfLurkers} are lurking: ";
		}

		$this->doPrivmsg($this->getEvent()->getNick(), $usersMessage . $lurkersMessage . $lurkerList);
	}

}
