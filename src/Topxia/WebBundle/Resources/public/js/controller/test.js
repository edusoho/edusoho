define(function(require, exports, module) {

    var TagChooser = require('./widget/tag-chooser/tag-chooser');

    var chooser = new TagChooser({
        element: '#tag-chooser',
        sourceUrl: 'xxxx',
        multi: true,
        items: []
    });

    chooser.on('choosed', function(items) {

    });


});