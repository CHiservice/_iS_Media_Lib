export type Column = {
	data: any,
	visible: boolean,
	width?: number,
	render?: (data: any, type: any, row: any) => string
};

