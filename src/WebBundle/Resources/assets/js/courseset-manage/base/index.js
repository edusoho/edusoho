import 'select2';
class Base
{
	constructor() {
		this.init();
	}

	init(){
		//init ui components
		$('#courseSet_tags').select2({
		    ajax: {
		        url: '/tag/match_jsonp#',
		        dataType: 'json',
		        quietMillis: 100,
		        data: function (term, page) {
		            return {
		                q: term,
		                page_limit: 10
		            };
		        },
		        results: function (data) {
		            var results = [];
		            $.each(data, function (index, item) {
		                results.push({
		                    id: item.name,
		                    name: item.name
		                });
		            });
		            return {
		                results: results
		            };
		        }
		    },
		    initSelection: function (element, callback) {
		        var data = [];
		        $(element.val().split(",")).each(function () {
		            data.push({
		                id: this,
		                name: this
		            });
		        });
		        callback(data);
		    },
		    formatSelection: function (item) {
		        return item.name;
		    },
		    formatResult: function (item) {
		        return item.name;
		    },
		    width: 'off',
		    multiple: true,
		    maximumSelectionSize: 20,
		    placeholder: Translator.trans('请输入标签'),
		    width: 'off',
		    multiple: true,
		    createSearchChoice: function () {
		        return null;
		    },
		    maximumSelectionSize: 20
		});
	}

}

new Base();
