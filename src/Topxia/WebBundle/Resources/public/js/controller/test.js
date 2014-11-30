define(function(require, exports, module) {

    var TagChooser = require('./widget/tag-chooser/tag-chooser');

    var chooser = new TagChooser({
        element: '#tag-chooser',
        sourceUrl: '/admin/knowledge/match',
        // sourceUrl: '/admin/tagset/match',
        // multi: true,
        multi: true,
        type: 'knowledge',
        items: []
    });

    chooser.on('choosed', function(items) {

        console.log(items);

    });

});