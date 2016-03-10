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
        var videoData = eval("(" + $('#videoUsedInfo').attr("value") +")");
        var smsData = eval("(" + $('#smsUsedInfo').attr("value") + ")");
        var liveData = eval("(" + $('#liveUsedInfo').attr("value") + ")");
        // var emailData = eval("(" + $('#emailUsedInfo').attr("value") + ")");
        if(videoData!=""){
          Morris.Line({
            element: 'storage-data-tab',
            data: videoData,
            xkey: 'date',
            units: "GB",
            ykeys: ['spacecount','transfercount'],
            labels: ['空间使用量','流量使用量']
          });
        }
        if(liveData!=""){
          Morris.Bar({
            element: 'live-data-tab',
            data: liveData,
            units: "人",
            xkey: 'date',
            ykeys: ['count'],
            labels: ['使用量']
          });
        }
        if(smsData!=""){
          Morris.Bar({
            element: 'sms-data-tab',
            data: smsData,
            units: "条",
            xkey: 'date',
            ykeys: ['count'],
            labels: ['使用量']
          });
        }
        // if(emailData!=""){
        //   Morris.Bar({
        //     element: 'email-data-tab',
        //     data: emailData,
        //     units: "封",
        //     xkey: 'date',
        //     ykeys: ['count'],
        //     labels: ['使用量']
        //   });
        // }
    };

});
