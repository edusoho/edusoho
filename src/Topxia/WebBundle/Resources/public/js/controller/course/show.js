define(function(require, exports, module) {

    exports.run = function() {
        require('./timeleft').run();
        $('#teacher-carousel').carousel({
            interval: 0
        });
        $('#teacher-carousel').on('slide.bs.carousel', function(e) {
            var teacherId = $(e.relatedTarget).data('id');

            $('#teacher-detail').find('.teacher-item').removeClass('teacher-item-active');
            $('#teacher-detail').find('.teacher-item-' + teacherId).addClass('teacher-item-active');
        });
        var Share = require('../../util/share');
        Share.create({
            selector: '.share',
            icons: 'itemsAll',
            display: 'dropdownWithIcon'
        });

        var reviewTabInited = false;

        if (!reviewTabInited) {
            var $reviewTab = $("#course-review-pane-show");

            $.get($reviewTab.data('url'), function(html) {
                $reviewTab.html(html);
                reviewTabInited = true;
            });

            $reviewTab.on('click', '.pagination a', function(e) {
                e.preventDefault();
                $.get($(this).attr('href'), function(html) {
                    $reviewTab.html(html);
                });
            });
        }

        var $body = $(document.body);

        $body.scrollspy({
            target: '.course-nav-tabs',
            offset: 120
        });

        $(window).on('load', function() {
            $body.scrollspy('refresh');
        });

        $('#course-nav-tabs').affix({
            offset: {
                top: 300
            }
        });

        $(window).bind("scroll", function() {
            var vtop = $(document).scrollTop();
            if (vtop > 300) {
                $('li.pull-right').css("display", "inline");
            } else {
                $('li.pull-right').css("display", "none");
            }

        });



        $('#course-nav-tabs').on('click', '.btn-index', function(event) {
            event.preventDefault();
            var position = $($(this).data('anchor')).offset();
            var top = position.top - 50;
            $(document).scrollTop(top);
        });

        $("#favorite-btn").on('click', function() {
            var $btn = $(this);
            $.post($btn.data('url'), function() {
                $btn.hide();
                $("#unfavorite-btn").show();
            });
        });

        $("#unfavorite-btn").on('click', function() {
            var $btn = $(this);
            $.post($btn.data('url'), function() {
                $btn.hide();
                $("#favorite-btn").show();
            });
        });

        $(".cancel-refund").on('click', function() {
            if (!confirm('真的要取消退款吗？')) {
                return false;
            }

            $.post($(this).data('url'), function() {
                window.location.reload();
            });
        });

        $('.become-use-member-btn').on('click', function() {
            $.post($(this).data('url'), function(result) {
                if (result == true) {
                    window.location.reload();
                } else {
                    alert('加入学习失败，请联系管理员！');
                }
            }, 'json').error(function() {
                alert('加入学习失败，请联系管理员！');
            });
        });

        $('.announcement-list').on('click', '[data-role=delete]', function() {
            if (confirm('真的要删除该公告吗？')) {
                $.post($(this).data('url'), function() {
                    window.location.reload();
                });
            }
            return false;
        });

        // fix for youku iframe player in firefox.
        $('#modal').on('shown.bs.modal', function() {
            $('#modal').removeClass('in');
        });


        var refreshAvtivityTimeLeft = function() {
            var activityEndTime = $("#price-after-discount").data("activityendtime");
            if (null != activityEndTime) {
                // console.log(activityEndTime);
                var strtotimestamp = function(datestr) {
                    var new_str = datestr.replace(/:/g, "-");
                    new_str = new_str.replace(/ /g, "-");
                    var arr = new_str.split("-");
                    var datum = new Date(Date.UTC(arr[0], arr[1] - 1, arr[2], arr[3], arr[4], arr[5]));
                    return (datum.getTime() / 1000);
                }
                activityEndTime = strtotimestamp(activityEndTime);

                var now = new Date;
                var month = ((now.getMonth() + 1) >= 10) ? (now.getMonth() + 1) : '0' + (now.getMonth() + 1);
                var day = (now.getDate() >= 10) ? now.getDate() : '0' + now.getDate();
                var hours = (now.getHours() >= 10) ? now.getHours() : '0' + now.getHours();
                var minutes = (now.getMinutes() >= 10) ? now.getMinutes() : '0' + now.getMinutes();
                var seconds = (now.getSeconds() >= 10) ? now.getSeconds() : '0' + now.getSeconds();
                var now = strtotimestamp(now.getFullYear() + '-' + month + '-' + day + ' ' + hours + ":" + minutes + ":" + seconds);

                // console.log(activityEndTime - now);

                var hoursLeft = Math.floor((activityEndTime - now) / 3600.0);
                var minutesLeft = Math.floor((activityEndTime - now) % 3600.0 / 60.0);
                var secondsLeft = Math.floor((activityEndTime - now) % 3600.0 % 60.0);


                console.log(hoursLeft);
                console.log(minutesLeft);
                console.log(secondsLeft);
                $("#hours-left").html(hoursLeft);
                $("#minutes-left").html(minutesLeft);
                $("#seconds-left").html(secondsLeft);
            }
        }
        refreshAvtivityTimeLeft();
    };

});