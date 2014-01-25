define(function(require, exports, module) {
    
    require('jquery.sortable');

    var Test = {

    	util: function(){

    		var objs = ['sortable','menuTotal'];

    		$.each(objs, function(key, name){

    	        eval('Test.'+name)();  
    		});

    	},

    	menuTotal : function(){
			var total         = 0;
			var questionTotal = 0;
			var questionType  = $('#myTab .active a').text();
			var questionConut = $('[name^=scores]:visible').length;

			$('[name^=scores][type=text]').each(function(){
			    total = Number($(this).val()) + Number(total);
			});

			$('[name^=scores]:visible').each(function(){
			    questionTotal = Number($(this).val()) + Number(questionTotal);
			});

			if(isNaN(total) || isNaN(questionTotal)){
				total = 0;
				questionTotal = 0;
			}
			
			var html = "试卷总分" + total + "分 " + questionType + questionConut + "题/ "+ questionTotal + "分";

			$('.score-text-alert').html(html);
    	},

        getMenuAllTotal : function(){
            var allTotal = new Array();

            $('#myTab li a').each(function (){

                var score  = 0;

                $($(this).attr('href')).find('[name^=scores][type=text]').each(function (index){
                    score = Number($(this).val()) + Number(score);
                });

                if (isNaN(score)) {
                    score = 0;
                }

                var count = $($(this).attr('href')).find('[name^=scores][type=text]').length;

                var total = {"name":$(this).text(), "score":score, "count":count};

                allTotal.push(total);
            });

            var allScore = 0;

            $('[name^=scores][type=text]').each(function(){
                allScore = Number($(this).val()) + Number(allScore);
            });

            if (isNaN(allScore)) {
                allScore = 0;
            }

            var count = $('[name^=scores][type=text]').length;

            var total = {"name":"总计", "score":allScore, "count":count};

            allTotal.push(total);
            
            return allTotal;
        },

    	sortable: function(){
    		var seq = 1;
    		$('#myTab li a').each(function(){
    			$($(this).attr('href')).find('tr').each(function(){
    				if($(this).data('type') == 'material'){
    					$(this).find('.seq').text('--');
    				}else{
    					$(this).find('.seq').text(seq);
    					seq ++;
    				}
    			});

    		});
    	},

        initSortable: function(id){

            $('#'+id).sortable({
                itemSelector: '.questionType',
                exclude: '.notMoveHandle',
                onDrop: function (item, container, _super) {
                    _super(item, container);
                    if (item.data('type') == 'material') {
                        var id = item.data('id');
                        var $subItems = $("#questionType-material").find("[data-type=" + id + "]");
                        $subItems.detach().insertAfter(item);
                    }
                    Test.sortable();
                },
            });
        },
    	

    };

    module.exports = Test;

});