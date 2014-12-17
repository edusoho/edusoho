define(function(require,exports,module) {
    var Widget = require('widget');
    var BaseChooser = require('./base-chooser');

    var CoursewareChooser = BaseChooser.extend({

        events: {
            "click [data-role=courseware-trigger]": "open",
            "click #import-courseware-url": "_onClickImportBtn"
        },

        _onClickImportBtn: function (e)
        {
            console.log('_onClickImportBtn')
            $url = this.element.find('#courseware-url-field').val();
            $re = this._getUrlRule();
            if ($re.test($url)) {
                $btn = $(e.currentTarget);
                $btn.button('loading');
                $.post($btn.data('url'),{url:$url},function(result){
                    if (result.status) {
                        $('[data-role=courseware-title]').html('<p class=\'text-danger\'>此URL有误，请检查</p>');
                        $btn.button('reset');
                        return;
                    };
                    $('[data-role=courseware-title]').html(result.title);
                    $btn.button('reset');
                });
            } else {
            }
        },

        getActiveRole: function ()
        {
            var $activeItem = " ";
            $coursewareTab = this.element.find('#coursewareTab');
            $coursewareTab.find('li').each(function(index,item){
                if ($(item).hasClass('active')) {
                    $activeItem = $(item).find('a').data('role');
                };
            });
            return $activeItem;
        }
    });


    module.exports = CoursewareChooser;
});