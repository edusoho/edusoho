define(function(require, exports, module) {
	require('jquery.autocomplete');

	var onReady = function(options){
		var type = options;
		if(type != 'tag' ) return;
		$("#publish_adv_categoryId").autocomplete({
	        url: '/advautocomplete/'+type+'?output=json',
	        sortFunction: function(a, b, filter) {
	            var f = filter.toLowerCase();
	            var fl = f.length;
	            var a1 = a.value.toLowerCase().substring(0, fl) == f ? '0' : '1';
	            var a1 = a1 + String(a.data[0]).toLowerCase();
	            var b1 = b.value.toLowerCase().substring(0, fl) == f ? '0' : '1';
	            var b1 = b1 + String(b.data[0]).toLowerCase();
	            if (a1 > b1) {
	                return 1;
	            }
	            if (a1 < b1) {
	                return -1;
	            }
	            return 0;
	        },
	        showResult: function(value, data) {
	            return '<span style="color:green">' + value + '</span>';
	        },
	        onItemSelect: function(item) {
	        },
	        mustMatch: true,
	        maxItemsToShow: 10,
	        selectFirst: false,
	        autoFill: false,
	        selectOnly: true,
	        minChars: 1,
	        remoteDataType: 'json'
	    });
	};

	exports.bootstrap = function(options) {
		$(onReady(options));
	};
});
