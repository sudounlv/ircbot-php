<?php

/**
 * Provides help for plugins
 *
 * @category Phergie
 * @package  Phergie_Plugin_Help
 * @author   Richard Hoppes <rhoppes@zappos.com>
 */
class Phergie_Plugin_SudoHelp extends Phergie_Plugin_Abstract
{
	public function onLoad() {
		$this->getPluginHandler()->getPlugin('Command');
	}

	protected function getHelpMessages() {
		$connection = $this->getConnection();
		$botNick = $connection->getNick();

		$helpMessages = array();
		$helpMessages['SudoHelp'] = "You can type !help {$botNick} in the channel you will get a list of commands.";
		$helpMessages['SudoHelloWorld'] = "You can type 'hello world', and {$botNick} will say it back!";
		$helpMessages['SudoSayHello'] = "You can type /msg {$botNick} hello (OR) hello {$botNick}, and {$botNick} will say hello back.";

		return $helpMessages;
	}

	public function onCommandHelp() {
		$event = $this->getEvent();
		$input = $event->getArgument(1);
		$nick = $event->getNick();
		$source = $event->getSource();
		$helpMessages = $this->getHelpMessages();

		$pattern = '/'.preg_quote($prefix).'\s*?help '.preg_quote($botNick).'/i';
		if (preg_match($pattern, $input)) {
			foreach ($this->plugins as $plugin) {
				$class = new ReflectionClass($plugin);
				if(isset($helpMessages[$plugin->getName()])) {
					$this->doPrivmsg($source, $helpMessages[$plugin->getName()]);
				}
			}
		}

		return;
	}

}
