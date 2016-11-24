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

		var $form = $("#courseset-create-form");
		$form.validate({
            onkeyup: false,
            rules: {
                title: {
                    required: true
                },
                expiryDays: {
                	required: '#expiryModeDays:checked',
                	digits:true
                },
                expiryStartDate: {
                	required: '#expiryModeDate:checked',
                	date:true
                },
                expiryEndDate: {
                	required: '#expiryModeDate:checked',
                	date:true,
                	after: '#expiryStartDate'
                }
            },
            messages: {
                title: "请输入教学计划课程标题",
                expiryDays: '请输入学习有效期',
                expiryStartDate: '请输入开始日期',
                expiryEndDate: {
                	required: '请输入结束日期',
                	after: '结束日期应晚于开始日期'
                }
            }
        });

        $.validator.addMethod(
	        "after",
	        function(value, element, params) {
	            console.log(value, element, params);
	            return this.optional(element) || $(params).value() > value;
	        },
	        "Please check your input."
		);

		
	}

}

new Base();
