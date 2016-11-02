/**
 * Created by Simon on 31/10/2016.
 */

var $parentiframe = $(window.parent.document).find('#task-manage-content-iframe');
$("#material a").click(function (e) {
    e.preventDefault();
    $(this).tab('show');
    $parentiframe.height($parentiframe.contents().find('body').height());
});

