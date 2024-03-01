<?php
class iS_Media_Lib extends iS_Module {
	protected $config = null;

	public function _init() {
		$this->config = iS_Media_Lib_Config::get_instance();

		if((int) iS_General_Settings::getModuleVal($this->config->get("modulName")) == 1) {
			if (iS_General_Settings::is_allowed() !== false) {
				new iS_Media_Lib_Settings();
			}
		}

		if((int) get_option("is_media_pdf_content_cache") == 1) {
			new iS_Media_Lib_PDF_Content_Cache();
		}
		
		new iS_Media_Lib_Tracking();
		new iS_Media_Lib_Backend();
	} // _init()
} // iS_Media_Lib()