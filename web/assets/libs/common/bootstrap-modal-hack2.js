define(function(require, exports, module) {
    $(document).on('click.modal.data-api', '[data-toggle="modal"]', function(e) {
        var imgUrl = app.config.loading_img_path;
        var $this = $(this),
            href = $this.attr('href'),
            url = $(this).data('url');
        if (url) {
            var $target = $($this.attr('data-target') || (href && href.replace(/.*(?=#[^\s]+$)/, '')));
            var $loadingImg = "<img src='" + imgUrl + "' class='modal-loading' style='z-index:1041;width:60px;height:60px;position:absolute;top:50%;left:50%;margin-left:-30px;margin-top:-30px;'/>";
            $target.html($loadingImg);
            $target.load(url);
        }
    });

    //同时存在多个modal时，关闭时还有其他modal存在，防止无法上下拖动
    $(document).on("hidden.bs.modal", "#attachment-modal", function() {
        if ($("#modal").attr('aria-hidden')) $(document.body).addClass("modal-open");
        if ($('#material-preview-player').length > 0) $('#material-preview-player').html("");
    });

    $('.modal').on('click', '[data-toggle=form-submit]', function(e) {
        e.preventDefault();
        $($(this).data('target')).submit();
    });

    $(".modal").on('click.modal-pagination', '.pagination a', function(e) {
        e.preventDefault();
        var $modal = $(e.delegateTarget);
        $.get($(this).attr('href'), function(html) {
            $modal.html(html);
        });
    });

    ;
    (function($, window, undefined) {
        // outside the scope of the jQuery plugin to
        // keep track of all dropdowns
        var $allDropdowns = $();

        // if instantlyCloseOthers is true, then it will instantly
        // shut other nav items when a new one is hovered over
        $.fn.dropdownHover = function(options) {
            // don't do anything if touch is supported
            // (plugin causes some issues on mobile)
            if ('ontouchstart' in document) return this; // don't want to affect chaining

            // the element we really care about
            // is the dropdown-toggle's parent
            $allDropdowns = $allDropdowns.add(this.parent());

            return this.each(function() {
                var $this = $(this),
                    $parent = $this.parent(),
                    defaults = {
                        delay: 100,
                        instantyCloseOthers: true
                    },
                    data = {
                        delay: $(this).data('delay'),
                        instantlyCloseOthers: $(this).data('close-others')
                    },
                    showEvent = 'show.bs.dropdown',
                    hideEvent = 'hide.bs.dropdown',
                    // shownEvent  = 'shown.bs.dropdown',
                    // hiddenEvent = 'hidden.bs.dropdown',
                    settings = $.extend(true, {}, defaults, options, data),
                    timeout;

                $parent.hover(function(event) {
                    // so a neighbor can't open the dropdown
                    if (!$parent.hasClass('open') && !$this.is(event.target)) {
                        // stop this event, stop executing any code
                        // in this callback but continue to propagate
                        return true;
                    }

                    openDropdown(event);
                }, function() {
                    timeout = window.setTimeout(function() {
                        $parent.removeClass('open');
                        $this.trigger(hideEvent);
                    }, settings.delay);
                });

                // this helps with button groups!
                $this.hover(function(event) {
                    // this helps prevent a double event from firing.
                    // see https://github.com/CWSpear/bootstrap-hover-dropdown/issues/55
                    if (!$parent.hasClass('open') && !$parent.is(event.target)) {
                        // stop this event, stop executing any code
                        // in this callback but continue to propagate
                        return true;
                    }

                    openDropdown(event);
                });

                // handle submenus
                $parent.find('.dropdown-submenu').each(function() {
                    var $this = $(this);
                    var subTimeout;
                    $this.hover(function() {
                        window.clearTimeout(subTimeout);
                        $this.children('.dropdown-menu').show();
                        // always close submenu siblings instantly
                        $this.siblings().children('.dropdown-menu').hide();
                    }, function() {
                        var $submenu = $this.children('.dropdown-menu');
                        subTimeout = window.setTimeout(function() {
                            $submenu.hide();
                        }, settings.delay);
                    });
                });

                function openDropdown(event) {
                    $allDropdowns.find(':focus').blur();

                    if (settings.instantlyCloseOthers === true)
                        $allDropdowns.removeClass('open');

                    window.clearTimeout(timeout);
                    $parent.addClass('open');
                    $this.trigger(showEvent);
                }
            });
        };

        $(document).ready(function() {
            // apply dropdownHover to all elements with the data-hover="dropdown" attribute
            $('[data-hover="dropdown"]').dropdownHover();
        });
    })(jQuery, this);

});