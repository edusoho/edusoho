define(function (require, exports, module) {
    var CourseSetClone = require('./clone');

    exports.run = function (options) {

        var csl = new CourseSetClone();


        $("#js-course-clone-btn").on('click',function (event) {
            event.preventDefault();
            csl.doClone($("#js-course-clone-btn").data('courseSetId'));
        });

    };

});
