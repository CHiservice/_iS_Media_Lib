import { l18n } from '../types/data.list.l18n';

export function formated_l18n(l18n: {[key: string]: string}):l18n {
	let fonmatted : l18n = {
		info           : l18n["info"] || "Showing page _PAGE_ of _PAGES_",
		infoEmpty      : l18n["info_empty"] || "No records available",
		infoFiltered   : l18n["info_filtered"] || "(filtered from _MAX_ total records)",
		lengthMenu     : l18n["length_menu"] || "Show _MENU_ entries",
		zeroRecords    : l18n["zero_records"] || "Nothing found.",
		loadingRecords : l18n["info_filtered"] || "Loading...",
		processing     : l18n[".processing"] || '<div class="iservice-loader loading"><div class="loader"></div></div>',
		search         : l18n["search"] || "Search",
		paginate       : {
			first    : l18n["first"] || "First",
			previous : l18n["previous"] || "Previous",
			next     : l18n["next"] || "Next",
			last     : l18n["last"] || "Last",
		},
		entries        : {
			_ : l18n["_"] || "Show _MENU_ entries",
			1:  l18n["1"] || "Show 1 entry",
		}
	};

	return fonmatted;
} // formated_l18n()