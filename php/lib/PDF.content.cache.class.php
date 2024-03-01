<?php

class iS_Media_Lib_PDF_Content_Cache {
	private $config          = null;
	static $pdf_cache_table = "is_media_lib_pdf_cache";

	public function __construct() {
		$this->config = iS_Media_Lib_Config::get_instance();

		$this->create_pdf_cache_table();

		add_filter("add_attachment", array($this, "insert_pdf_chache"), 10, 1);
		add_filter("delete_attachment",  array($this, "delete_pdf_cache"), 10, 2);

		add_action("rest_api_init", function () {
			// "/register/(?P<id>\d+)/(?P<number>[a-zA-Z0-9-]+)"
			register_rest_route("is_media_lib/", "pdf", array(
				"methods"  => "POST",
				"callback" => array($this, "search_pdf"),
			));
		});
	} // __construct

	function create_pdf_cache_table() {
		global $wpdb;

		$table_name      = $wpdb->prefix.iS_Media_Lib_PDF_Content_Cache::$pdf_cache_table;
		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE IF NOT EXISTS $table_name (
			ID INT NOT NULL AUTO_INCREMENT,
			attachment_id INT,
			content TEXT,
			PRIMARY KEY (ID),
			KEY `attachment_id` (`attachment_id`)
		) $charset_collate";
	
		require_once(ABSPATH.'wp-admin/includes/upgrade.php');
		dbDelta($sql);
	} // create_pdf_cache_table()

	function insert_pdf_chache($ID) {
		if(get_post_mime_type($ID) != "application/pdf") {
			return $ID;
		}

		global $wpdb;
		require "composer/vendor/autoload.php";

		$parser  = new \Smalot\PdfParser\Parser();
		$pdf     = $parser->parseFile(get_attached_file($ID));
		$content = $pdf->getText();

		$table_name = $wpdb->prefix.iS_Media_Lib_PDF_Content_Cache::$pdf_cache_table;
		$saved      = $wpdb->get_results("SELECT * FROM $table_name WHERE attachment_id = ".$ID, ARRAY_A);
		if(is_array($saved) && count($saved) > 0) {
			$query = "UPDATE ".$table_name." SET content = '".esc_sql($content)."' WHERE attachment_id = ".$ID;
		} else {
			$query = "INSERT INTO ".$table_name." (attachment_id, content) VALUES (".$ID.", '".esc_sql($content)."')";
		}
		$wpdb->query($query);

		return $ID;
	} // after_upload()

	function delete_pdf_cache($ID, $post) {
		global $wpdb;

		$table_name = $wpdb->prefix.iS_Media_Lib_PDF_Content_Cache::$pdf_cache_table;
		$wpdb->delete($table_name, array("ID" => $ID));
	} // delete_pdf_cache()

	public function search_pdf(WP_REST_Request $request) {
		$data = $request->get_body();
		try {
			$data          = json_decode($data, true);
			$tracking_data = $this->_get_pdf_cache($data);
			unset($data["start"]);
			unset($data["length"]);
			$total         = count($this->_get_pdf_cache($data));
			return array(
				"draw"            => $data["draw"]++,
				"recordsTotal"    => $total,
				"recordsFiltered" => $total,
				"data"            => $tracking_data,
			);
		} catch (Exception $e) {
		}
	} // search_pdf()

	private function _get_pdf_cache($data) {
		global $wpdb;
		$mapping = array(
			"file"          => "`p`.`post_title`",
			"file_date"     => "`p`.`post_date`",
			"content"       => "`t`.`content`",
			"post_id"       => "`p`.`ID`",
			"attachment_id" => "`p`.`ID`",
		);
		
		$filters = array();
		foreach($data["columns"] AS $i => $column) {
			if($column["search"]["value"] != "") {
				$filters[] = "AND ".$mapping[$column["data"]]." LIKE '%".esc_sql($column["search"]["value"])."%'";
			}
		}

		$query = "SELECT ";
		foreach ($mapping as $as => $col) {
			$query .= $col." AS `".$as."`, ";
		}
		$query = substr($query, 0, -2)." FROM ".$wpdb->prefix.iS_Media_Lib_PDF_Content_Cache::$pdf_cache_table." AS `t`
		LEFT JOIN ".$wpdb->prefix."posts AS `p` ON `t`.`attachment_id` = `p`.`ID` 
		WHERE 1=1 ".
		implode(" ", $filters);

		if(array_key_exists("search", $data) && is_array($data["search"]) && array_key_exists("value", $data["search"]) && trim($data["search"]["value"]) != "") {
			$query .= "AND (`p`.`post_title` LIKE '%".esc_sql($data["search"]["value"])."%' 
				OR `t`.`content` LIKE '%".esc_sql($data["search"]["value"])."%'
			)";
		}

		if(array_key_exists("order", $data) && is_array($data["order"]) && count($data["order"]) > 0) {
			$query .= " ORDER BY ";
			foreach($data["order"] AS $order) {
				$query .= $mapping[$data["columns"][$i]["data"]]." ".$order["dir"].", ";
			}
			$query = substr($query, 0, -2);
		}

		if(array_key_exists("start", $data) && array_key_exists("length", $data)) {
			$query .= " LIMIT ".$data["start"].", ".$data["length"];
		}

		return $GLOBALS["wpdb"]->get_results($query, ARRAY_A);
	} // _get_pdf_cache()
} // iS_Media_Lib_PDF_Content_Cache{}
