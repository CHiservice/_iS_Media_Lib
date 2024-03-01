<?php
class iS_Media_Lib_Config extends iS_Module_Config {
	private static $instance;

	private function __clone() {}

	public static function get_instance() {
		if (!iS_Media_Lib_Config::$instance instanceof self) {
			iS_Media_Lib_Config::$instance = new self();
		}
		return iS_Media_Lib_Config::$instance;
	} // get_instance()

	public function __construct() {
		$this->set("version", parent::get_version("iS_Media_Lib"));
		$this->set("modulName", "iS_Media_Lib");
		$this->set("customPrefix", "iS_");
	} // __construct()
} // iS_Config()