$('#text-activity').perfectScrollbar();
$('#text-activity').perfectScrollbar('update');

if ($('#text-activity').data('disableCopy')) {
	document.onselectstart = new Function("return false");
	document.oncontextmenu = new Function("return false");
}
