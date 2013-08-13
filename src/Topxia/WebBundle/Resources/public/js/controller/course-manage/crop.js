define(function(require, exports, module) {
    require("jquery.jcrop");
    exports.run = function() {
        require('./header').run();

        function updateCoords(c){
            $('#x').val(c.x);
            $('#y').val(c.y);
            $('#w').val(c.w);
            $('#h').val(c.h);
        };

        $("#pic2crop").Jcrop({
          onSelect: updateCoords
        });
        
    };
  
});