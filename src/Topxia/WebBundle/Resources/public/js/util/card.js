define(function (require, exports, module) {

    $(".js-user-card").on("mouseenter", function () {
        var _this = $(this);
        if (_this.data('bind') !== 'true') {
            $.get(_this.data('cardUrl'),function(html) {
                _this.popover({
                    trigger: 'manual',
                    placement: 'auto top',
                    html: 'true',
                    content: html,
                    template: '<div class="popover es-card"><div class="arrow"></div><class="popover-content">'+ html +'</div></div>',
                    container: 'body',
                    animation: false
                });
                _this.data('bind', 'true');
                _this.popover("show");
                bindCardEvent($('.user-card'));
                $(".popover").on("mouseleave", function () {
                    $(_this).popover('hide');
                });
            });         
        } else {
            _this.popover("show");
        }
       
    }).on("mouseleave", function () {
        var _this = $(this);
        setTimeout(function () {
            if (!$(".popover:hover").length) {
                _this.popover("hide")
            }
        }, 100);
    });

    
    function bindCardEvent($card)
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
        });
    }

});