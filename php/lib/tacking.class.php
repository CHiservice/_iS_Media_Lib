<?php

class iS_Media_Lib_Tracking {
	private $config         = null;
	static $tracking_table = "is_media_lib_tracking";

	public function __construct() {
		$this->config = iS_Media_Lib_Config::get_instance();

		$this->create_tracking_table();

		if(count(iS_Media_Lib_Settings::get_enabled_media_tracking_pts()) > 0) {
			add_filter("save_post", array($this, "save_data"), 200, 2);
			add_action("deleted_post", array($this, "remove_data"), 10, 2);
		}

		add_action("rest_api_init", function () {
			// "/register/(?P<id>\d+)/(?P<number>[a-zA-Z0-9-]+)"
			register_rest_route("is_media_lib/", "tracking", array(
				"methods"  => "POST",
				"callback" => array($this, "search_tracking"),
			));
		});
	} // __construct

	function create_tracking_table() {
		global $wpdb;

		$table_name      = $wpdb->prefix.self::$tracking_table;
		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE IF NOT EXISTS $table_name (
			ID INT NOT NULL AUTO_INCREMENT,
			attachment_id INT,
			post_id INT,
			usage_type VARCHAR(100),
			metabox_id TEXT,
			field_id TEXT,
			field_type TEXT,
			PRIMARY KEY (ID),
			KEY `attachment_id` (`attachment_id`),
			KEY `post_id` (`post_id`)
		) $charset_collate";
	
		require_once(ABSPATH."wp-admin/includes/upgrade.php");
		dbDelta($sql);
	} // create_pdf_cache_table()

	function save_data($ID, $post) {
		$this->_clear($ID);

		if(iS_Media_Lib_Settings::is_media_tracking_pt_enabled($post->post_type) == 1) {
			$attachments = $this->_get_html_attachments($post->post_content);
			$this->_insert($ID, $attachments, "post_content");

			$thumbnail_id = get_post_thumbnail_id($ID);
			$this->_insert($ID, $attachments, "thumbnail");

			$meta_box_registry = rwmb_get_registry("meta_box");
			$args              = array(
				"object_type" => "post",
				"post_types"  => array($post->post_type),
			);
			$meta_boxes = $meta_box_registry->get_by($args);

			foreach($meta_boxes AS $meta_box) {
				$meta_box = $meta_box->meta_box;
				if(is_array($meta_box) && array_key_exists("fields", $meta_box)) {
					foreach($meta_box["fields"] AS $field) {
						if($field["type"] == "group") {
							foreach($field["fields"] AS $subfield) {
								$attachments = $this->_get_field_attachments($ID, $subfield, $field);
								$this->_insert($ID, $attachments, "metabox", $meta_box["id"], $field["id"]."|".$subfield["id"], "group|".$subfield["type"]);
							}
						} else {
							$attachments = $this->_get_field_attachments($ID, $field);
							$this->_insert($ID, $attachments, "metabox", $meta_box["id"], $field["id"], $field["type"]);
						}
					}
				}
			}
		}
	} // save_data()

	function remove_data($ID, $post) {
		$this->_clear($ID);
	} // remove_data()

	static function get_attachment_tracking_count($ID) {
		global $wpdb;

		$table_name = $wpdb->prefix.self::$tracking_table;
		$count      = $wpdb->get_var("SELECT COUNT(*) FROM $table_name WHERE attachment_id = $ID");

		return $count;
	} // get_attachment_tracking_count(

	function clear_post_type($post_type) {
		global $wpdb;

		$table_name = $wpdb->prefix.self::$tracking_table;
		$sql = "DELETE `t`
			FROM `$table_name` AS `t`
			LEFT JOIN `".$wpdb->prefix."posts` AS `p` ON `t`.`post_id` = `p`.`ID`
			WHERE `p`.`post_type` = '$post_type'";
		$wpdb->query($sql);
	} // clear_post_type()
	
	private function _clear($ID) {
		global $wpdb;

		$table_name = $wpdb->prefix.self::$tracking_table;
		$wpdb->delete($table_name, array("post_id" => $ID));
	} // _clear()

	private function _insert($ID, $attachments, $usage_type, $metabox_id = null, $field_id = null, $field_type = null) {
		global $wpdb;

		$table_name = $wpdb->prefix.self::$tracking_table;
		foreach($attachments AS $attachment_id) {
			$wpdb->insert($table_name, array(
				"post_id"       => $ID, 
				"attachment_id" => $attachment_id,
				"usage_type"    => $usage_type,
				"metabox_id"    => $metabox_id,
				"field_id"      => $field_id,
				"field_type"    => $field_type,
			));
		}
	} // _insert()

	private function _get_field_attachments($ID, $field, $parent_field = null) {
		$media = array();
		switch($field["type"]) {
			case "wysiwyg":
				$value = "";
				if(!is_null($parent_field)) {
					$group = rwmb_get_value($parent_field["id"], array(), $ID);
					if(is_array($group)) {
						foreach($group AS $sub_value) {
							if(is_array($sub_value) && array_key_exists($field["id"], $sub_value)) {
								$value .= $sub_value[$field["id"]];
							}
						}
					}
				} else {
					$value = rwmb_get_value($field["id"], array(), $ID);
				}

				$media = $this->_get_html_attachments($value);
				break;
			case "image_advanced":
			case "file_advanced":
				if(!is_null($parent_field)) {
					$value = rwmb_get_value($parent_field["id"], array(), $ID);
					if(is_array($value)) {
						foreach($value AS $sub_value) {
							if(is_array($sub_value) && array_key_exists($field["id"], $sub_value)) {
								foreach($sub_value[$field["id"]] AS $img) {
									$media[] = (int) $img;
								}
							}
						}
					}
				} else {
					$value = rwmb_get_value($field["id"], array(), $ID);
					$media = array_keys($value);
				}
				break;
			case "image_upload":
			case "file_upload":
			case "video":
				if(!is_null($parent_field)) {
					$value = rwmb_get_value($parent_field["id"], array(), $ID);
					if(is_array($value)) {
						foreach($value AS $sub_value) {
							if(is_array($sub_value) && array_key_exists($field["id"], $sub_value)) {
								foreach($sub_value[$field["id"]] AS $img) {
									$media[] = (int) $img;
								}
							}
						}
					}
				} else {
					$value = rwmb_get_value($field["id"], array(), $ID);
					$media = array_keys($value);
				}
				break;
		}

		return $media;
	} // _get_field_attachments()

	private function _get_html_attachments($value) {
		if(is_null($value) || trim($value) == "") {
			return array();
		}

		$dom = new DOMDocument;
		libxml_use_internal_errors(true);
		$dom->loadHTML($value);

		$imageTags = $dom->getElementsByTagName("img");
		foreach ($imageTags as $imgTag) {
			$url = $imgTag->getAttribute("src");
			if(strstr($url, "wp-content/uploads")) {
				$attachment_id = attachment_url_to_postid($url);
				if ($attachment_id != 0) {
					$media[] = $attachment_id;
				} else {
					$attachment_id = $this->_get_image_id_by_name(basename($url));
					if (!is_null($attachment_id)) {
						$media[] = $attachment_id;
					}
				}
			}
		}
		$fileTags = $dom->getElementsByTagName("a");
		foreach ($fileTags as $aTag) {
			$url = $aTag->getAttribute("href");
			if(strstr($url, "wp-content/uploads")) {
				$attachment_id = attachment_url_to_postid($url);
				if ($attachment_id != 0) {
					$media[] = $attachment_id;
				}
			}
		}

		return $media;
	}

	private function _get_image_id_by_name($filename) {
		// attachment_url_to_postid buggy on small images
		global $wpdb;

		return $wpdb->get_var("SELECT post_id FROM $wpdb->postmeta WHERE meta_key='_wp_attachment_metadata' AND meta_value LIKE '%$filename%'");
	} // _get_image_id_by_name()

	public function search_tracking(WP_REST_Request $request) {
		$data = $request->get_body();
		try {
			$data          = json_decode($data, true);
			$tracking_data = $this->_get_attachment_tracking($data);
			foreach($tracking_data AS $i => $tracking) {
				$thumbnail = wp_get_attachment_image_src($tracking["attachment_id"], "thumbnail");
				if ($thumbnail) {
					$tracking_data[$i]["thumbnail"] = $thumbnail[0];
				} else {
					$tracking_data[$i]["thumbnail"] = null;
				}
			}
			unset($data["start"]);
			unset($data["length"]);
			$total         = count($this->_get_attachment_tracking($data));
			return array(
				"draw"            => $data["draw"]++,
				"recordsTotal"    => $total,
				"recordsFiltered" => $total,
				"data"            => $tracking_data,
			);
		} catch (Exception $e) {
		}
	} // search_tracking()

	private function _get_attachment_tracking($data) {
		global $wpdb;
		$mapping = array(
			"mime_type"     => "`a`.`post_mime_type`",
			"file"          => "`a`.`post_title`",
			"file_date"     => "`a`.`post_date`",
			"post_title"    => "`p`.`post_title`",
			"post_type"     => "`p`.`post_type`",
			"post_date"     => "`p`.`post_date`",
			"usage_type"    => "`t`.`usage_type`",
			"field_type"    => "`t`.`field_type`",
			"field_id"      => "`t`.`field_id`",
			"attachment_id" => "`t`.`attachment_id`",
			"post_id"       => "`t`.`post_id`",
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
		$query = substr($query, 0, -2)." FROM ".$wpdb->prefix.iS_Media_Lib_Tracking::$tracking_table." AS `t`
		LEFT JOIN ".$wpdb->prefix."posts AS `a` ON `t`.`attachment_id` = `a`.`ID`
		LEFT JOIN ".$wpdb->prefix."posts AS `p` ON `t`.`post_id` = `p`.`ID` 
		WHERE 1=1 ".
		implode(" ", $filters);

		if(array_key_exists("search", $data) && is_array($data["search"]) && array_key_exists("value", $data["search"]) && trim($data["search"]["value"]) != "") {
			$query .= "AND (`a`.`post_title` LIKE '%".esc_sql($data["search"]["value"])."%' 
				OR `a`.`post_mime_type` LIKE '%".esc_sql($data["search"]["value"])."%'
				OR `p`.`post_title` LIKE '%".esc_sql($data["search"]["value"])."'%
				OR `p`.`post_type` LIKE '%".esc_sql($data["search"]["value"])."%'
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
	} // _get_attachment_tracking_count()

	public static function get_mime_types() {
		global $wpdb;
		$query = "SELECT 
			`p`.`post_mime_type`
		FROM `".$wpdb->prefix."posts` AS `p`
		LEFT JOIN `".$wpdb->prefix.iS_Media_Lib_Tracking::$tracking_table."` AS `t` ON `t`.`attachment_id` = `p`.`ID`
		WHERE `t`.`attachment_id`
		GROUP BY `p`.`post_mime_type`
		ORDER BY `p`.`post_mime_type`";

		$dataArr = $GLOBALS["wpdb"]->get_results($query, ARRAY_A);
		$result = array();
		if(is_array($dataArr)) {
			foreach($dataArr AS $data) {
				$result[] = $data["post_mime_type"];
			}
		}

		return $result;
	} // get_mime_types()
} // iS_Media_Lib_Tracking{}