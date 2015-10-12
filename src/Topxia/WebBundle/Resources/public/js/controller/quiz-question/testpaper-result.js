define(function(require, exports, module) {
    exports.run = function() {

        window.addEventListener('load', function() {
          setTimeout(function() {
            window.addEventListener('popstate', function() {
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
          }, 0);
          window.history.pushState('forward', null, './result');
        });
    };
});