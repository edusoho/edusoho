define(function (require, exports, module) {

    $.fn.cardTip = function(options) {
        var defaults = {    
            Event : "mouseover",                                 //触发响应事件
        };
        var $this = $(this);

        var options = $.extend(defaults,options);
        $this.on(options.Event, function(){
            var $card = $("#user-card-"+ $this.data('userId'));
            if ($card.length > 0) {
                displayCard($card);
            } else {
                $.get($this.data('cardUrl'),function(html) {
                    var $card = $(html);
                    $("body").append($card);
                    bindCardEvent($card);
                    displayCard($card);
                });         
            }
            
        });
        
        function displayCard($card)
        {
            $('.user-card').hide();
            var h = $card.height();
            var w = $this.width();
            var offset = $this.offset();
            $card.css({
                "left":offset.left+ w/2,
                "top":offset.top - h
            }).show();
        }

        function bindCardEvent($card)
        {
            $card.on(options.Event, function(){
                $(this).show();                                  
            }).on("mouseout", function(){               
                $(this).hide();                                                     
            }).on('click', '.follow-btn', function(){
                var $btn = $(this);
                $.post($btn.data('url'), function() {
                    $btn.hide();
                    $card.find('.unfollow-btn').show();
                });
            }).on('click', '.unfollow-btn', function(){
                var $btn = $(this);
                $.post($btn.data('url'), function() {
                    $btn.hide();
                    $card.find('.follow-btn').show();
                });
            });
        }

    }
});