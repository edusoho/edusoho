define(function(require, exports, module) {
	require('fullcalendar');
	require('momentmin');
	//require('json');
	exports.run = function() {
		var datajson=$('#datajson').data('fullcalendarevents');
		var today=$('#datajson').data('today');
		$('#calendar').fullCalendar({
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
			businessHours: true, // display business hours
			editable: false,
			eventLimit: true,
			theme: false,
			events: datajson,
			eventClick: function(event,element) {
        		if (event.url) {
	    			window.open(event.url);
	    			return false;
        		}
        	},
		});
	};
});
