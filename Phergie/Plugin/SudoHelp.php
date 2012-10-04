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
		$botNick = $this->getConnection()->getNick();

		$helpMessages = array();
		$helpMessages['SudoHelp'] = "You can type !help {$botNick} in the channel you will get a list of commands.";
		$helpMessages['SudoHelloWorld'] = "You can type 'hello world', and {$botNick} will say it back!";
		$helpMessages['SudoSayHello'] = "You can type /msg {$botNick} hello (OR) hello {$botNick}, and {$botNick} will say hello back.";
		$helpMessages['SudoLastFive'] = "You can type !last5 <username> to see the last five messages that user sent to the channel.";
		$helpMessages['SudoLurkers'] = "You can type !lurkers and see a list of users who have not spoken in the channel for 30 minutes.";

		return $helpMessages;
	}

	public function onCommandHelp() {
		$botNick = $this->getConnection()->getNick();
		$prefix = $this->getConfig('command.prefix');
		$input = $this->getEvent()->getArgument(1);
		$source = $this->getEvent()->getSource();
		$helpMessages = $this->getHelpMessages();

		$pattern = '/'.preg_quote($prefix).'\s*?help '.preg_quote($botNick).'/i';
		if (preg_match($pattern, $input)) {
			foreach ($this->plugins as $plugin) {
				if(isset($helpMessages[$plugin->getName()])) {
					$this->doPrivmsg($source, $helpMessages[$plugin->getName()]);
				}
			}
		}
	}

}
