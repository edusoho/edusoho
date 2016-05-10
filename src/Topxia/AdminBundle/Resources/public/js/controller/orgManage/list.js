define(function(require, exports, module) {
    require('jquery-plugin/jquery.treegrid/0.3.0/css/jquery.treegrid.css');
    require('jquery-plugin/jquery.treegrid/0.3.0/js/jquery.treegrid');
    require('jquery-plugin/jquery.treegrid/0.3.0/js/jquery.cookie');
    exports.run = function() {
        $('.tree').treegrid({
            'initialState': 'collapsed',
          //  'saveState': true,
        });
    };
});