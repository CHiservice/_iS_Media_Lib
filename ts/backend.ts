(function ($) {
	// @ts-ignore
	var media_l18n = is_media_lib_backend_vars.l18n;

	$("body").append(
		$('<div />').attr({
			"id"          : "modal-overlay",
			"class"       : "modal",
			"area-hidden" : "true"
		}).append(
			$('<div/>').attr({
				"class" : "modal_box"
			}).append(
				$('<table />').attr({
					"class" : "wp-list-table widefat fixed striped table-view-list"
				}).append(
					$('<thead />').append(
						$('<tr />').append(
							$('<th />').text(media_l18n.post_title)
						).append(
							$('<th />').text(media_l18n.post_type)
						).append(
							$('<th />')
						)
					)
				).append(
					$('<tbody />')
				)
			)
		)
	);
	var $modal = $('#modal-overlay');

	function unique_id(){
		return Date.now().toString(36) + Math.random().toString(36);
	} // unique_id();

	$(".table-view-list.media .tracking_detail").on("click touch", (e) => {
		e.preventDefault();
		$modal.find(".modal_box tbody").empty();

		var id = e.target.dataset["id"];
		if(parseInt(e.target.dataset["count"]) == 0) {
			return;
		}
		// @ts-ignore
		var ajax_url = is_media_lib_backend_vars.ajax_url;
		$.ajax({
			url     : ajax_url + id + "/?no_cache=" + unique_id(),
			type    : "GET",
			success : function (response) {
				if(response && response.length > 0) {
					for(var i = 0; i < response.length; i++) {
						$modal.find(".modal_box tbody").append(

							$('<tr />').append(
								$('<td />').append(
									$('<a />').attr({
										"href" : response[i]["href"],
									}).text(response[i]["post_title"])
								)
							).append(
								$('<td />').text(response[i]["post_type"])
							).append(
								$('<td />').append(
									$('<i />').attr({
										"class" : "usage-type " + response[i]["usage_type"],
										"title" : response[i]["usage_type"],
									})
								)
							)
						);
					}
					// @ts-ignore
					openModal(document.getElementById("modal-overlay"));
				}
			} // success()
		}); // $.ajax()
	});
	// @ts-ignore
})(window.jQuery);
