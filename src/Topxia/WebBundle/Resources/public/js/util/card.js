define(function (require, exports, module) {

    $(".js-user-card").on("mouseenter", function () {
        var _this = $(this);
        var loadingHtml = '<div class="card-body"><div class="card-loader"><span class="loader-inner"><span></span><span></span><span></span></span> 名片加载中</div>';
        if (!_this.data('html')) {
            _this.popover({
                trigger: 'manual',
                placement: 'auto top',
                html: 'true',
                content: function(){
                   if (!_this.data('html')) {
                        return loadingHtml;
                   }  else {
                        return _this.data('html')
                   }
                },
                template: '<div class="popover es-card"><div class="arrow"></div><div class="popover-content"></div></div>',
                container: 'body',
                animation: true
            });
            _this.popover("show");
            _this.on('show.bs.popover', function () {
                $(".popover").hide();
            });

            $.get(_this.data('cardUrl'),function(html) {
                _this.data('html', html);
                bindCardEvent($('.es-card'));
                $(".popover").on("mouseleave", function () {
                    $(_this).popover('hide');
                });
                setTimeout(function(){
                    _this.popover("show");
                }, 400);
               
            });         
        } else {
            _this.popover("show");
        }
       
        bindMsgBtn($('.es-card'), _this);

    }).on("mouseleave", function () {
        var _this = $(this);
        setTimeout(function () {
            if (!$(".popover:hover").length) {
                _this.popover("hide")
            }
        }, 100);
    });

    
    function bindCardEvent($card, self)
    {
        $card.on('click', '.follow-btn', function(){
            console.log(2);
            var $btn = $(this);
            $.post($btn.data('url')).always(function(){
                $btn.hide();
                $card.find('.unfollow-btn').show();
            });
        }).on('click', '.unfollow-btn', function(){
            var $btn = $(this);
            $.post($btn.data('url')).always(function(){
                $btn.hide();
                $card.find('.follow-btn').show();
            });
        })
    }

    function bindMsgBtn($card, self){
        $card.on('click','.direct-message-btn', function(){
            $(self).popover('hide');
        })
    }
    

});