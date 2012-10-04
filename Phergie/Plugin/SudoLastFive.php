<?php

class Phergie_Plugin_SudoLastFive extends Phergie_Plugin_Abstract {

	protected $messages = array();

	public function onLoad() {
		$this->getPluginHandler()->getPlugin('Command');
		$this->joinTime = time();
	}

	/**
	 * Get all messages recorded for nick
	 * @param $nick
	 * @return array
	 */
	protected function getUserMessages($nick) {
		$retVal = array();
		if(isset($this->messages[$nick])) {
			$retVal = $this->messages[$nick];
		}
		return $retVal;
	}

	/**
	 * Record message for nick
	 * @param $nick
	 * @param $content
	 */
	protected function addUserMessage($nick, $content) {
		if(!isset($this->messages[$nick])) {
			$this->messages[$nick] = array();
		}
		if(sizeof($this->messages[$nick]) == 5) {
			array_shift($this->messages[$nick]);
		}
		$this->messages[$nick][] = array('content' => $content, 'time' => time());;
	}

	/**
	 * Record all messages sent to channel or bot
	 */
	public function onPrivmsg() {
		$this->addUserMessage($this->getEvent()->getNick(), trim($this->getEvent()->getArgument(1)));
	}

	/**
	 * Command to retrieve last 5 messages for specified nick
	 */
	public function onCommandLast5() {
		$botNick = $this->getConnection()->getNick();
		$commandPrefix = $this->getConfig('command.prefix');
		$input = $this->getEvent()->getArgument(1);
		$requesterNick = $this->getEvent()->getNick();

		$pattern = '/'.preg_quote($commandPrefix).'\s*?last5 (.*)/i';
		if (preg_match($pattern, $input, $matches)) {
			$commandNick = $matches[1];

			$messages = $this->getUserMessages($commandNick);
			$messageCount = sizeof($messages);
			if($messageCount > 0) {
				$this->doPrivmsg($requesterNick, "The last {$messageCount} messages for {$commandNick}: ");
				foreach($messages as $message) {
					$this->doPrivmsg($requesterNick, $message['content'] . " at " . Date('Y-m-d H:i:s', $message['time']));
				}
			} else {
				$this->doPrivmsg($requesterNick, "No messages logged for {$commandNick}");
			}
		}
	}

	/**
	 * Command to list lurkers
	 */

}
