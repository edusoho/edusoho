define(function(require, exports, module) {
    require('../user/organizationZtree.js').run();
    var Morris = require('morris');
    var Notify = require('common/bootstrap-notify');

    exports.run = function() {
        
         var passNum = $('#passNum').val();
         var unPassNum = $('#unPassNum').val();
        //start 饼图
        Morris.Donut({
          element: 'graph',
          data: [
            {value: passNum, label: '通过'},
            {value: unPassNum, label: '未通过'},
          ],
          labelColor: '#060',
          colors: [
            '#0BA462',
            '#FACC2E',
          ]
        
        });

        //end 


        /*
        *点击编辑，直接编辑其他成绩项
        */
        $('#transcripts-list').on('click','#score-edit',function(){
          $this = $(this);
          $otherScore = $this.parents('tr').children("[name='otherScore']");
          var oldOtherScore = $otherScore.html();
          $otherScore.attr('contenteditable','');
          $otherScore.focus();
          /*
          *其他失去焦点，异步提交修改
          */
          $otherScore.blur(function(){
            var otherScore = $otherScore.html();
            var id = $this.data('id');
            if(!isNaN(otherScore)){
              $data = {
              'id' : id,
              'otherScore' : otherScore
              }
              $.post($this.data('url'),$data,function(data){
                  var $totalScore = $this.parents('tr').children("[name='totalScore']");
                  var $credit = $this.parents('tr').children("[name='credit']");
                  $totalScore.html(data.userScore.totalScore);
                  if(data.userScore.totalScore >= data.courseScoreSetting.standardScore){
                    $credit.html(data.courseScoreSetting.credit);
                  }else{
                    $credit.html('--');
                  }
              });
            }else{
               $otherScore.html(oldOtherScore);
            }
            
          });
        })

        


        var  listChange=function(){
        var inputGroupText2=$("input[name=major]");
        var  TextLink2=$(".js-grades ul a");
        TextLink2.click(function(event){
           event.preventDefault();
          var $this=$(this);
          inputGroupText2.val($this.text());
        })
        }();

        var matchPage = /.*page=(\d+)/; 
        var page;
        var $form = $('#score-from');
        var $url = $form.attr('action');

        $('#score_publish').on('click',function(event){
             event.preventDefault()
             var $this = $(this);
             $.post($this.attr('data-url'),function(data){
                 var msg = data.msg;
                 if(data.type == 'danger'){
                    Notify.danger(msg);
                 }else{
                    Notify.success(msg);
                    $this.remove();
                 }

             },'json');
        });

        $('#search').on('click', function(event) {
            event.preventDefault()
            var exports = $('#score_export');
            var staffNo = $('#staffNo').val();
            var organizationId = $('#organizationId').val();
            exports.attr('href', exports.attr('data-url')+'?staffNo='+staffNo+'&organizationId='+organizationId);
        });

        $form.on('click', '#search', function() {
            event.preventDefault()
            var staffNo = $('#staffNo').val();
            var organizationId = $('#organizationId').val();
            $data = {
              'staffNo':staffNo,
              'organizationId':organizationId          
            }
            $.get($url, $data,function(html){
              $('#transcripts-list').html(html);
            });
        });

        $('#transcripts-list').on('click','.pagination a',function(event){
            event.preventDefault()
            var currentPage;
            var href = $(this).attr('href');
            if ((page = matchPage .exec(href)) !== null) {
                currentPage = page[1];
            }
            var staffNo = $('#staffNo').val();
            var organizationId = $('#organizationId').val();
            $data = {
              'page':currentPage,
              'staffNo':staffNo,
              'organizationId':organizationId
            }
            $.get($url,$data,function(html){
             $('#transcripts-list').html(html);
              
            });

        });

        

    };

});                

                