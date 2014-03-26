define(function(require, exports, module) {

    exports.run = function() {
        $("#begin-update").click(function() {
            var urls = $(this).data();
            console.log(urls);
            checkEnvironment(urls.checkEnvironmentUrl);
        });

    };

    function checkEnvironment(url) {
        $.ajax(url, {
            async: false,
            dataType: 'json',
            type: 'POST'
        }).done(function(data, textStatus, jqXHR) {
            console.log('down', data);
        }).fail(function(jqXHR, textStatus, errorThrown) {

        });
    }

});