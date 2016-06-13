define(function(require, exports, module) {
    exports.run = function() {
    	var ids=[];
        $( "input[name='select']:checked").each(function(){
        	var id = $(this).parents('tr').attr('id');
        	ids.push(id.split('-').pop());
        });
        console.log(ids);//JSON.stringify()
        $("#batch-ids").val(ids);
    };
});