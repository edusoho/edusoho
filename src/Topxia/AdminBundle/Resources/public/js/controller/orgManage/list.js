define(function(require, exports, module) {
    require('jquery-plugin/jquery.treegrid/0.3.0/css/jquery.treegrid.css');
    require('jquery-plugin/jquery.treegrid/0.3.0/js/jquery.treegrid');
    require('jquery-plugin/jquery.treegrid/0.3.0/js/jquery.cookie');
    var TreeGrid =require('edusoho/treegrid/1.0.0/treegrid-debug')
    exports.run = function() {
        new TreeGrid({element: '.tree'}).create({
            'initialState': 'collapsed',
            'saveState': true
        });
    };
});