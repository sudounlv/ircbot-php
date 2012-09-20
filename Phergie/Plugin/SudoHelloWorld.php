<?php

class Phergie_Plugin_SudoHelloWorld extends Phergie_Plugin_Abstract
{
	/**
	 * When user says hello to the bot, say hello back!
	 * If the message is private, the bot will respond privately
	 * @return void
	 */
	public function onPrivmsg() {
		$event = $this->getEvent();
		$input = $event->getArgument(1);
		$source = $event->getSource();
		$prefix = $this->getConfig('command.prefix');

		if (strcasecmp($input, 'hello world') == 0) {
			$this->doPrivmsg($source, "Hello World!");
		}
	}

}
