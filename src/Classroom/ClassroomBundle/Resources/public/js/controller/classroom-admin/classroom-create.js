define(function(require, exports, module) {

    exports.run = function() {
        if($("#create-classroom").val() != ''){
            if($("#showable-open").data('showable')==1){
                $("#showable-open").attr('checked','checked');
            }
            else{
                $("#showable-close").attr('checked','checked');
            }
            if($("#buyable-open").data('buyable')==1){
                $("#buyable-open").attr('checked','checked');
            }
            else{
                $("#buyable-close").attr('checked','checked');
            }
        }
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