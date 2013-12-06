define(function(require, exports, module) {

    
    require('wookmark');
    

    exports.run = function() {


        $("#pic-list li").wookmark({ 
            container:$("#pic-list"), 
            offset:0
              
        }); 



    };

});