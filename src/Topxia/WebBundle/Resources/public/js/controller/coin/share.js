define(function(require, exports, module) {

    $('body').on('click', '.js-social-share', function() {
        var $btn = $(this);
        var type = $btn.data('share');
        var params = $btn.parents('.js-social-share-params').data();

        var url = '';
        if($(".point-share-url").length>0) {
            $.post($(".point-share-url").val(), function () {});
        }
        switch(type) {
            case 'weibo':
                url = weibo(params);
                window.open(url);
                break;
            case 'qzone':
                url = qzone(params);
                window.open(url);
                break;
            case 'qq':
                url = qq(params);
                window.open(url);
                break;
            case 'weixin':
                weixin($btn, params);
                break;
        }

    });

    function weixin($btn, params)
    {
        if ($('.weixin-share-modal').length == 0) {
            $('body').append(makeWeixinModal(params));
            var $modal = $('.weixin-share-modal');
            $modal.on('show.bs.modal', function(){
                $modal.find('.weixin-share-qrcode').empty();
                $modal.find('.weixin-share-loading').show();
                $modal.find('.weixin-share-qrcode').html('<img src="' + $btn.data('qrcodeUrl') + '">' );
                $modal.find('.weixin-share-qrcode img').load(function(){
                    $modal.find('.weixin-share-loading').hide();
                });
            });
        }

        $('.weixin-share-modal').modal('show');
    }

    function makeWeixinModal(params)
    {
        var html = '';
        html += '<div class="modal fade weixin-share-modal" tabindex="-1" role="dialog" aria-hidden="true">';
        html += '  <div class="modal-dialog modal-sm">';
        html += '    <div class="modal-content">';
        html += '      <div class="modal-header">';
        html += '        <button type="button" class="close" data-dismiss="modal" aria-label="关闭"><span aria-hidden="true">×</span></button>';
        html += '        <h4 class="modal-title">分享到微信朋友圈</h4>';
        html += '      </div>';
        html += '      <div class="modal-body">';
        html += '        <p class="weixin-share-loading" style="text-align:center;">正在加载二维码...</p>';
        html += '        <p class="weixin-share-qrcode text-center"></p>'
        html += '        <p class="text-muted text-center"><small>打开微信，点击底部的“发现”，</small><br><small>使用 “扫一扫” 即可将网页分享到我的朋友圈。</small></p>';
        html += '      </div>'
        html += '    </div>';
        html += '  </div>';
        html += '</div>';
        return html;
    }


    function weibo(params)
    {
        var query = {};
        query.url = params.url;
        query.title = params.message;
        
        if (params.picture != '') {
            if (params.picture.indexOf('://') != -1){
                query.pic = params.picture; 
            } else {
                query.pic = document.domain + params.picture; 
            }
        }
        
        return 'http://service.weibo.com/share/share.php?' + buildUrlQuery(query);
    }

    function qzone(params)
    {
        var query = {};
        query.url = params.url;
        query.title = params.title;
        query.summary = params.summary;
        query.desc = params.message;
        if (params.picture != '') {
            query.pics = params.picture;
        }
        
        return 'http://sns.qzone.qq.com/cgi-bin/qzshare/cgi_qzshare_onekey?' + buildUrlQuery(query);
    }

    function qq(params)
    {
        var query = {};

        query.url = params.url;
        query.title = params.title;
        query.summary = params.summary;
        query.desc = params.message;
        if (params.picture != '') {
            query.pics = params.picture;
        }
        
        return 'http://connect.qq.com/widget/shareqq/index.html?' + buildUrlQuery(query);
    }

    function buildUrlQuery (query)
    {
        var queryItems = [];
        for( var q in query ){
            queryItems.push(q + '=' + encodeURIComponent( query[q] || '' ) )
        }

        return queryItems.join('&');
    }

});