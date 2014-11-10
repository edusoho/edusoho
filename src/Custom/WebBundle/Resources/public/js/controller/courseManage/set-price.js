define(function(require, exports, module) {
    
    exports.run = function() {
        
        $('#set-price').on('click',function(){

            $('#price-modal').modal('show');

        });
        
        $('#price-modal').on('shown.bs.modal', function (e) {
            
            $('#sure').on('click',function(){

                var price=$('[name=set-price]').val();
                
                if(!isNaN(price)){
                    
                    $('.price').attr('value',price);

                    $('#price-modal').modal('hide');
                }

            });
        });
    };

});

