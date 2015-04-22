define(function(require, exports, module) {

    require('jquery.cycle2');

    exports.run = function() {
        $('.homepage-feature').cycle({
            fx: "scrollHorz",
            slides: "> a, > img",
            log: "false",
            pauseOnHover: "true"
        });

        $('input:checkbox[name="coursesTypeChoices"]').on("change", function() {

            $(this).siblings('input').prop('checked', false);

            $(this).parents("form").submit();
        });

        $(".js-course-sort a").click(function() {
            if (!$(this).hasClass("active")) {
                $(this).addClass("active").siblings().removeClass("active");
                var postData = {
                    id: $(this).attr("data-id")
                };
                $.ajax({
                    url: "",
                    type: "POST",
                    data: postData,
                    success: function(data) {

                    }
                });
            }
        });

        $(".index-cuorse-cat").on("click", ".js-index-cuorse-cat a", function(e) {
            var $that = $(this);
            var postData = {
                id: $(this).attr("data-id")
            };
            if (!$that.hasClass("active")) {
                $that.addClass("active").siblings().removeClass("active");
                $.ajax({
                    url: "",
                    type: "POST",
                    data: postData,
                    success: function(data) {

                    }
                });
            }
        });


        var carouselCreat = function() {
                var $this = $(".carousel");
                if($this.length >= 1){
                    $this.each(function(i,d){
                        var $thisItem = $(d).find(".item");
                        var thisId = $(d).attr("id");
                        for (var i = 0; i < $thisItem.length; i++) {
                            var html = '';
                            if (i == 0) {
                                html = '<li data-target=#'+thisId+' data-slide-to="0" class="active"></li>';
                            }else {
                                html = '<li data-target=#'+thisId+' data-slide-to="'+i+'"></li>';
                            }
                            $(d).find(".carousel-indicators").append(html);
                        }
                    })
                }
            }()

    };

});