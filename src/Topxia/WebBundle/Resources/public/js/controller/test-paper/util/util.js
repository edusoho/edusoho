define(function(require, exports, module) {

    var Test = {

    	utli: function(){

    		var objs = ['sortable','menuTotal'];

    		$.each(objs, function(key, name){

    	        eval('Test.'+name)();  
    		});

    	},

    	menuTotal : function(){
			var total = 0;
			var questionTotal = 0;
			var questionType = $('#myTab .active a').text();
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
    	

    };

    module.exports = Test;

});