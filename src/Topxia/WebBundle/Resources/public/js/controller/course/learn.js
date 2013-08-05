define(function(require, exports, module) {

    var LessonDashboard = require('../lesson/lesson-dashboard');

    exports.run = function() {
        
        var dashboard = new LessonDashboard({
            element: '#lesson-dashboard'
        }).render();

    };

});