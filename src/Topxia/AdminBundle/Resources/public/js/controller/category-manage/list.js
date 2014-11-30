define(function(require, exports, module) {

    var Notify = require('common/bootstrap-notify');
    require("jquery.bootstrap-datetimepicker");
    var TagChooser = require('tag-chooser');
    require("$");
    exports.run = function() {
        var $container = $('#quiz-table-container');
        window.$ = $;
        require('../../util/short-long-text')($container);
        require('../../util/batch-select')($container);
        require('../../util/batch-delete')($container);
        require('../../util/item-delete')($container);

        var chooser = new TagChooser({
          element: '#tagchooser',
          sourceUrl: $('#tagchooser').data('source'),
/*          queryUrl: 'data-choosed-tags.json',*/
/*          matchUrl: 'data-match-tags.json?q={{query}}',*/
          maxTagNum: 4,
          choosedTags: []
        });

        chooser.on('change', function(tags) {

          console.log('change tags', tags);

        });

        chooser.on('existed', function(existTag){
          console.log('existed');
        });

    };

});