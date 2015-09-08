define(function(require, exports, module) {

    exports.run = function() {
        var showable = document.getElementsByName("showable");
        $("#showable-close").click(function(){
            $("#buyable-open").attr('disabled','disabled');
            $("#buyable-close").attr('disabled','disabled');
        })
        $("#showable-open").click(function(){
            $("#buyable-open").removeAttr('disabled');
            $("#buyable-close").removeAttr('disabled');
        })
        
	}
});