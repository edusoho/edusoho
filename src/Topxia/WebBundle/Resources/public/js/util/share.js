define(function(require, exports, module) {

    exports.create = function(object) {

        function contains(arr, str) {
            var i = arr.length;
            while (i--) {
               if (arr[i] === str) {
               return true;
               }
            }
            return false;
        }

        function changeWeixinQrcodeHeight(){
            setTimeout(changeWeixinQrcodeHeight,0);
            if ($('#bdshare_weixin_qrcode_dialog').length > 0 ) {
                $('#bdshare_weixin_qrcode_dialog').css('height','310px')
            };
        }
        
        var select=object.selector;
        var name=object.icons;
        var type=object.display;

        var itemsAll = ['tsina','qq','weixin','renren','more'];
        var itemsByqq = ['qq','more'];

        var config=[];
        config.itemsAll=itemsAll;
        config.itemsByqq=itemsByqq;

        if(type=="dropdown"){
            var html='<div class="dropdown pull-right"><a class="dropdown-toggle text-muted "  href="#" id="dropdownMenu1" data-toggle="dropdown" >分享到<span class="caret"></span></a><ul class="dropdown-menu" role="menu" aria-labelledby="dropdownMenu1"><div class="bdsharebuttonbox" style="padding:1px;">';
        }
        else if(type=="dropdownWithIcon"){
            var html='<div class="dropdown pull-right" style="padding:1px;"><a class="dropdown-toggle btn btn-link"  href="javascript:" id="dropdownMenu1" data-toggle="dropdown" ><span class="glyphicon glyphicon-share"></span> 分享到</a><ul class="dropdown-menu" role="menu" aria-labelledby="dropdownMenu1"><div class="bdsharebuttonbox" style="padding:1px;">';
        }else{
            var html='<ul style="padding:1px;"><div class="bdsharebuttonbox" >';
        }

        if(contains(config[name],'qq')){
             html+='<a href="#" class="bds_qzone" data-cmd="qzone" title="分享到QQ空间" style="margin:3px 1px 2px 6px;"></a>';
        }

        if(contains(config[name],'tsina')){
             html+='<a href="#" class="bds_tsina" data-cmd="tsina" title="分享到新浪微博" style="margin:3px 2px 2px 6px;"></a>';
        }

        if(contains(config[name],'weixin')){
             html+='<a href="#" class="bds_weixin" data-cmd="weixin" title="分享到微信" style="margin:3px 2px 2px 6px;"></a>';
        }

        if(contains(config[name],'renren')){
             html+='<a href="#" class="bds_renren" data-cmd="renren" title="分享到人人网" style="margin:3px 2px 2px 6px;"></a>';
        }

        if(contains(config[name],'more')){
             html+='<a href="#" class="bds_more" data-cmd="more" style="margin:3px 2px 2px 6px;"></a>';
        }

        var bdText=$(select).attr("data-bdText");
            html+='</div>';

        window._bd_share_config={
                "common":{
                        "bdSnsKey":{},
                        "bdText":bdText,
                        "bdDesc":"  ",
                        "bdMini":1,
                        "bdComment":" ",
                        "bdMiniList" :  ['douban','tieba','tqq','tqf','sqq','mail','baidu','taobao'],
                        "bdPic":"",
                        "bdStyle":"0",
                        "bdSign":"normal"
                        },
                "share":{
                        "bdSize":24
                }
        };

        with(document)0[(getElementsByTagName('head')[0]||body).appendChild(createElement('script')).src='http://bdimg.share.baidu.com/static/api/js/share.js?v=89860593.js?cdnversion='+ (new Date()).valueOf()];
        html+='</ul>';

        $(select).html(html);

        changeWeixinQrcodeHeight();
    }

});