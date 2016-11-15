let defaultEvent = {
    init: function() {
        this.checkbox();
        this.radio();
    },
    checkbox : function() {
        
        $('body').on('click.checkbox.init','.kz-checkbox-inline input[type="checkbox"]',function(e){
            const $dom = $(e.currentTarget);

            if($dom.parent().hasClass('checked')) {
                $dom.parent().removeClass('checked');

            }else {
                $dom.parent().addClass('checked');
            }
        })

        //  $('body').on('click.radio.init','.kz-checkbox-inline',function(e){
        //     const $dom = $(e.currentTarget);
        //     console.log(999)
            
        //     if(!$dom.hasClass('checked')) {
        //         $dom.addClass('checked');
        //     }else {
        //         $dom.removeClass('checked');
        //     }
        // })
    },
    radio: function() {

        // $('body').on('click.radio.init','.kz-radio-inline',function(e){
        //     console.log(111)
        //     const $dom = $(e.currentTarget);

        //     if(!$dom.hasClass('checked')) {
        //         $dom.siblings('.kz-radio-inline').removeClass('checked')
        //         $dom.addClass('checked');
        //     }
        // })

        $('body').on('click.radio.init','.kz-radio-inline input[type="radio"]',function(e){
            const $dom = $(e.currentTarget);

            if(!$dom.parent().hasClass('checked')) {

                $dom.parent().siblings('.kz-radio-inline').removeClass('checked');
                $dom.parent().addClass('checked');
            }

        })
    }
} 

export default defaultEvent;