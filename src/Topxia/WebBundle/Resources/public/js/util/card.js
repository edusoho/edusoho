define(function (require, exports, module) {

    if(!navigator.userAgent.match(/(iPhone|iPod|Android|ios|iPad)/i)){
        bindCardEvent('.js-card-content');
        $(".js-user-card").on("mouseenter", function () {


            var _this = $(this);
            var userId = _this.data('userId');
            var loadingHtml = '<div class="card-body"><div class="card-loader"><span class="loader-inner"><span></span><span></span><span></span></span>'+ Translator.trans('名片加载中')+'</div>';

            var timer = setTimeout(function(){

                function callback(html) {
                        
                    _this.popover('destroy');

                    if ($('#user-card-' + userId).length == 0) {
                        if ($('body').find('#user-card-store').length > 0 ) {
                            $('#user-card-store').append(html);
                        } else {
                            $('body').append('<div id="user-card-store" class="hidden"></div>');
                            $('#user-card-store').append(html);
                        }
                    }

                    _this.popover({
                        trigger: 'manual',
                        placement: 'auto top',
                        html: 'true',
                        content: function(){
                            return html;
                        },
                        template: '<div class="popover es-card"><div class="arrow"></div><div class="popover-content"></div></div>',
                        container: 'body',
                        animation: true
                    });

                    _this.popover("show");

                    _this.data('popover', true);
                
                    $(".popover").on("mouseleave", function () {
                        $(_this).popover('hide');
                    });
                    
                };

                if ($('#user-card-' + userId).length == 0 || !_this.data('popover')) {
                    
                    function beforeSend () {

                        _this.popover({
                            trigger: 'manual',
                            placement: 'auto top',
                            html: 'true',
                            content: function(){
                                return loadingHtml;
                            },
                            template: '<div class="popover es-card"><div class="arrow"></div><div class="popover-content"></div></div>',
                            container: 'body',
                            animation: true
                        });

                        // _this.popover("show");

                    };

                    $.ajax ({
                        type:"GET",
                        url: _this.data('cardUrl'),
                        dataType: "html",
                        beforeSend: beforeSend,
                        success: callback
                    });

                } else {
                    var html = $('#user-card-' + userId).clone();
                    callback(html);
                    // _this.popover("show");
                }
               
                bindMsgBtn($('.es-card'), _this);


            },300);

            _this.data('timerId', timer);

        }).on("mouseleave", function () {
     
            var _this = $(this);
      
            setTimeout(function () {
       
                if (!$(".popover:hover").length) {
       
                    _this.popover("hide");
       
               }
       
           }, 100);

            clearTimeout(_this.data('timerId'));

        });
    }

    
    function bindCardEvent(selector)
    {
        $('body').on('click', '.js-card-content .follow-btn', function(){
            var $btn = $(this);
            var loggedin = $btn.data('loggedin');
            if(loggedin == "1"){
                showUnfollowBtn($btn);
            }
            $.post($btn.data('url'));
        }).on('click', '.js-card-content .unfollow-btn', function(){
            var $btn = $(this);
            showFollowBtn($btn);
            $.post($btn.data('url'));
        })
    }

    function bindMsgBtn($card, self){
        $card.on('click','.direct-message-btn', function(){
            $(self).popover('hide');
        })
    }

    function showFollowBtn($btn)
    {
        $btn.hide();
        $btn.siblings('.follow-btn').show();
        $actualCard = $('#user-card-'+ $btn.closest('.js-card-content').data('userId'));
        $actualCard.find('.unfollow-btn').hide();
        $actualCard.find('.follow-btn').show();
    }

    function showUnfollowBtn($btn)
    {
        $btn.hide();
        $btn.siblings('.unfollow-btn').show();
        $actualCard = $('#user-card-'+ $btn.closest('.js-card-content').data('userId'));
        $actualCard.find('.follow-btn').hide();
        $actualCard.find('.unfollow-btn').show();
    }
    

});