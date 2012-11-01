<?php

abstract class Phergie_Plugin_SudoAbstract extends Phergie_Plugin_Abstract
{
	protected $persistence = false;

	protected function restoreState($whereToPutSavedState) {
		if(!$this->persistence)
			return false;

		$database = null;
		try {
			$database = Phergie_Database_MySQL::getHandle($this->config['database']['host'], $this->config['database']['username'], $this->config['database']['password'], $this->config['database']['name']);
		} catch (Exception $ex) {
			return false;
		}

		$state = $database->select("SELECT variable_value FROM plugin_state WHERE plugin_name = '".get_class($this)."' AND variable_name = 'state'");

		if(is_array($state) && sizeof($state) > 0 && $state[0]['variable_value'] != null) {
			$this->$whereToPutSavedState = unserialize($state[0]['variable_value']);
		}

	}

	protected function persistState($whatToSave) {
		if(!$this->persistence)
			return false;

		$database = null;
		try {
			$database = Phergie_Database_MySQL::getHandle($this->config['database']['host'], $this->config['database']['username'], $this->config['database']['password'], $this->config['database']['name']);
		} catch (Exception $ex) {
			return false;
		}

		$state = $database->select("SELECT id FROM plugin_state WHERE plugin_name = '".get_class($this)."' AND variable_name = 'state'");

		$id = 0;
		if(is_array($state) && sizeof($state) > 0) {
			$id = $state[0]['id'];
		}

		if(is_array($state) && sizeof($state) > 0) {
			$variables = array('id' => $id, 'variableValue' => serialize($this->$whatToSave));
			$database->select("UPDATE plugin_state SET variable_value = val(variableValue) WHERE id = val(id)", $variables);
		} else {
			$variables = array('variableValue' => serialize($this->$whatToSave));
			$database->select("INSERT INTO plugin_state (plugin_name, variable_name, variable_value) VALUES ('".get_class($this)."', 'state', val(variableValue))", $variables);
		}
	}
}
