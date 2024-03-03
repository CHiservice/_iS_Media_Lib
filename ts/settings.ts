(function ($) {
	// @ts-ignore
	var ajax_url          : string = is_media_lib_settings_js_vars.ajax_url;
	// @ts-ignore
	var pt_names          : any = is_media_lib_settings_js_vars.pts;
	var $pdf_cache_status = $('.media-lib-pdf-cache-status');
	var $tracking_status  = $('.media-lib-media-tracking-status');

	function unique_id(){
		return Date.now().toString(36) + Math.random().toString(36);
	} // unique_id();

	if($pdf_cache_status.length > 0) {
		function write_pdf_cache():void {
			$.ajax({
				url     : ajax_url + "init_pdf_cache?no_cache=" + unique_id(),
				type    : "GET",
				success : function (response) {
					$pdf_cache_status.addClass("hidden");
					if($('#is_media_pdf_content_cache').attr("checked") != undefined && response && response["statistic"]) {
						$pdf_cache_status.find(".count").text(response["statistic"][0] + " / " + response["statistic"][1]);
						$pdf_cache_status.removeClass("hidden");
					}

					if(response && response["status"] && response["status"] != "done") {
						setTimeout(function() {
							// loop
							write_pdf_cache();
						}, 500);
					}
				}
			});
		} // write_pdf_cache()
		write_pdf_cache();
	} // if($pdf_cache_status.length > 0)

	if($tracking_status.length > 0) {
		function write_tracking():void {

			$.ajax({
				url     : ajax_url + "init_tracking/?no_cache=" + unique_id(),
				type    : "GET",
				success : function (response) {
					if(response) {
						$tracking_status.addClass("hidden");
						var restart = false;
						$tracking_status.find(".counts").empty();
						for(var pt in response) {
							if($('#is_media_track_attachment_' + pt).attr("checked") != undefined) {
								$tracking_status.find(".counts").append(response[pt][0] + " / " + response[pt][1] + " (" + pt_names[pt]  + ")<br>");
								$tracking_status.removeClass("hidden");

								if(response[pt][0] < response[pt][1]) {
									restart = true;
								}
							}
						}
						if(restart == true) {
							setTimeout(function() {
								// loop
								//write_tracking();
							}, 500);
						}
					}
				} // success()
			}); // $.ajax()
		} // write_tracking()
		write_tracking();
	} // if($tracking_status.length > 0)
	// @ts-ignore
})(window.jQuery);
