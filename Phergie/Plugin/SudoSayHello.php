<?php

class Phergie_Plugin_SudoSayHello extends Phergie_Plugin_Abstract
{
	/**
	 * Respond when a user says hello!
	 *
	 * <code>this is where command goes</code>
	 *
	 * @return void
	 */
	public function onPrivmsg() {
		$connection = $this->getConnection();
		$botNick = $connection->getNick();

		$event = $this->getEvent();
		$input = trim($event->getArgument(1));
		$nick = $event->getNick();
		$source = $event->getSource();

		if(strcasecmp($source, $nick) == 0) {
			// Private message
			$pattern = '/hello.*/i';
			if (preg_match($pattern, $input)) {
				$this->doPrivmsg($source, "Hello {$nick}!");
			}
		} else {
			// Channel message
			$pattern = '/hello '.preg_quote($botNick).'/i';
			if (preg_match($pattern, $input)) {
				$this->doPrivmsg($source, "Hello {$nick}!");
			}
		}
	}

}
