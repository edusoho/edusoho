define(function(require, exports, module) {
    exports.run = function() {
        if (window.history && window.history.pushState) {
            $(window).on('popstate', function () {
                var hashLocation = location.hash;
                var hashSplit = hashLocation.split("#!/");
                var hashName = hashSplit[1];
                if (hashName !== '') {
                    var hash = window.location.hash;
                    if (hash === '') {
                        location.reload();
                    }
                }
            });
        window.history.pushState('forward', null, './result');
        }
    };
});