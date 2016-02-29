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
        // var a = new Object();
        // a = eval((eval("("+$('#videoUsedSpaceInfo').attr("value")+")")));
        // // a.push(eval("("+$('#videoUsedTransferInfo').attr("value")+")"));
        // console.log(a);
        // console.log(eval ("(" +eval ("(["+$('#videoUsedSpaceInfo').attr("value")+")") + "],["+eval ("("+$('#videoUsedSpaceInfo').attr("value")+")")+"])"));
        // var sss=$('#videoUsedSpaceInfo').attr("value") + ","+$('#videoUsedTransferInfo').attr("value");

        var videoData = eval("(" + $('#videoUsedInfo').attr("value") +")");
        var smsData = eval("(" + $('#smsUsedInfo').attr("value") + ")");
        var liveData = eval("(" + $('#liveUsedInfo').attr("value") + ")");
        var emailData = eval("(" + $('#emailUsedInfo').attr("value") + ")");
        console.log(videoData);
         // console.log(smsData);
        //var videosData =[{"date":"2015-03","count":5},{"date":"2015-04","count":9},{"date":"2015-05","count":77},{"date":"2015-06","count":10},{"date":"2015-07","count":40},{"date":"2015-08","count":30},{"date":"2015-09","count":20}];
        if(videoData!=""){
          Morris.Line({
            element: 'storage-data-tab',
            data: videoData,
            xkey: 'date',
            ykeys: ['spacecount','transfercount'],
            labels: ['空间使用量','流量使用量']
          });
          // Morris.Line.setdata(videosData);
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
        if(emailData!=""){
          Morris.Bar({
            element: 'email-data-tab',
            data: emailData,
            xkey: 'date',
            ykeys: ['count'],
            labels: ['使用量']
          });
        }
    };

});
