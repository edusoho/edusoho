define(function(require, exports, module) {
    require("chineserp-jquery");
    require("chineserp-jquery-css");
    exports.run = function(){
        var $form = $('#address-from');
        $form.find('[data-role =region-picker]').regionPicker().on('picked.rp', function(e, regions){
          console.log(e, regions);
        });
    }
});