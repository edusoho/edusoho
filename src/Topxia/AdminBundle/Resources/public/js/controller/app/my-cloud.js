define(function(require, exports, module) {
	var Morris=require("morris");
	require("jquery.bootstrap-datetimepicker");
    exports.run = function() {

        $("#sms-reason-tips").popover({
            html: true,
            trigger: 'focus',
            placement: 'right',
            content: $("#sms-reason-tips-html").html(),
        });

        var videoData = eval ("(" + $('#videoUsedInfo').attr("value") + ")");
        var smsData = eval ("(" + $('#smsUsedInfo').attr("value") + ")");
        var liveData = eval ("(" + $('#liveUsedInfo').attr("value") + ")");
        console.log(videoData);
        //console.log(videoData==""?[{"date":"2015-03","count":5},{"date":"2015-04","count":9},{"date":"2015-05","count":77},{"date":"2015-06","count":10},{"date":"2015-07","count":40},{"date":"2015-08","count":30},{"date":"2015-09","count":20}]:"");
        if(videoData!=""){
          Morris.Bar({
            element: 'storage-data-tab',
            data: videoData,
            xkey: 'date',
            ykeys: ['count'],
            labels: ['使用量']
          });
        }
        if(liveData!=""){
          Morris.Bar({
            element: 'live-data-tab',
            data: liveData,
            xkey: 'date',
            ykeys: ['count'],
            labels: ['使用量']
          });
        }
        if(smsData!=""){
          Morris.Bar({
            element: 'sms-data-tab',
            data: smsData,
            xkey: 'date',
            ykeys: ['count'],
            labels: ['使用量']
          });
        }
        Morris.Bar({
          element: 'email-data-tab',
          data: [{"date":"2015-03","count":5},{"date":"2015-04","count":9},{"date":"2015-05","count":77},{"date":"2015-06","count":10},{"date":"2015-07","count":40},{"date":"2015-08","count":30},{"date":"2015-09","count":20}],
          xkey: 'date',
          ykeys: ['count'],
          labels: ['使用量']
        });
    };

});
