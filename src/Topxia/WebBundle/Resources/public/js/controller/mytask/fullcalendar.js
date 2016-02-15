define(function(require, exports, module) {
	require('fullcalendar');
	require('momentmin');
	var Widget = require('widget');
	exports.run = function() {
		var datajson=$('#datajson').data('fullcalendarevents');
		var today=$('#datajson').data('today');
		var $calendar = $('#calendar');
		var Cal  = Widget.extend({
			//attr:{
			//},
			setup:function(){
				var calendar = this.element.fullCalendar({
					header: {
						left: 'prev,next today',
						center: 'title',
						right: 'month'
					},
					buttonText: {    
		                today: '今天',
		                month: '月',
		            },
					lang:'zh-cn',
					allDayDefault:true,
					defaultDate: today,
					forceEventDuration: true,
					businessHours: true, // display business hours
					editable: false,
					eventLimit: false,
					theme: false,
					events: datajson,
					displayEventEnd: true,
					resizable:true,
					eventClick: function(event,element) {
		        		if (event.url) {
			    			window.open(event.url);
			    			return false;
		        		}
		        	},
		        	eventMouseover: function( event, jsEvent, view ) {
		        	 	$(this).attr({'title': event.title,"data-toggle":"tooltip"});
		        	},
				});	
			},
			/*render:function(){
				var cal = new Cal({
            		element: $calendar
        		});
			}*/
		});
		var cal = new Cal({
            element: $calendar
        }).render();
	};
});
