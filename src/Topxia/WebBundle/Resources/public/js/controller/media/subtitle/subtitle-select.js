define(function(require,exports,module){

    var convertStatus = require('./convert-status-map');

    var Select = {
        init:function(options){
            this.$el = $(options.id);
            if(!this.$el.length){
                throw new Error('id 不存在');
            }
            this.options = [];
            this.optionsLimit = options.optionsLimit || false;
            this.eventManager = {};
            this.initParent();
            this.initEvent();
        },
        initParent:function(){
            var _self = this;
            $documentFragment = $(document.createDocumentFragment());
            $documentFragment.append(this.templete());
            this.$el.append($documentFragment);
            this.$parentDom = $('.track-select-parent');
            this.$list = $('.track-selcet-list');
            this.$dataShow = this.$parentDom.find('.data-show');
            this.$open = this.$parentDom.find('.track-selcet-open-arrow');
            this.$close = this.$parentDom.find('.track-selcet-close-arrow');
            this.$showBox = this.$parentDom.find('.track-select-show');
        },
        initEvent:function(){
            var _self = this;
            this.$parentDom
            .delegate('.track-selcet-open-arrow','click',this.handleOpen.bind(this))
            .delegate('.track-selcet-close-arrow','click',this.handleClose.bind(this))
            .delegate('.delete','click',this.handleDelete.bind(this))
            .delegate('.select-item','click',function(){
                $(this).siblings().removeClass('active');
                $(this).addClass('active');
                var name = $(this).find('.value').html();
                var url = $(this).find('.value').attr('url');
                _self.setValue({name:name,url:url});
                _self.handleClose();
            })
            this.$showBox.on('click',this.toggle.bind(this));
            this.on('valuechange',function(){
                this.$dataShow.html(this.getValue().name);
                this.$dataShow.attr('title',this.getValue().name);
            });
            this.on('listchange',function(){
                if(this.optionsLimit && this.options.length >= this.optionsLimit ){
                    this.trigger('optionlimit');
                }
                this.$list.html(this.getOptionsStr());
                this.setValue(this.getDefaultOption())
            });
            this.on('optionempty',this.handleOptionEmpty.bind(this))
        },
        templete:function(){
            return ''+
                '<div class="track-select-parent">'+
                    '<div class="track-select-show">'+
                        '<div class="data-show" title="'+ this.getDefaultOption().name +'"></div>'+
                        '<span class="track-selcet-open-arrow">'+
                            '<i class="es-icon es-icon-keyboardarrowdown"></i>'+
                        '</span>'+
                        '<span class="track-selcet-close-arrow" style="display:none;">'+
                            '<i class="es-icon es-icon-keyboardarrowup"></i>'+
                        '</span>'+
                    '</div>'+
                    '<ul class="track-selcet-list" style="display:none;">'+
                        this.getOptionsStr()+
                    '</ul>'+
                '</div>';
        },
        getDefaultOption: function() {
            if(this.options.length){
                return this.options[0];
            }else{
                this.open ? this.handleClose() : '';
                return false;
            }
        },
        getOptionsStr:function(){
            var _self = this;
            if(!this.options.length){
                this.trigger('optionempty');
            }
            var optionsStr = '';
            this.options.map(function(option,index){
                optionsStr += '<li class="select-item">'+
                    '<div class="value" title="'+ option.name +'" url="'+option.url+'">'+
                        option.name+
                    '</div>'+
                    '<span class="convertStatus convert-'+ option.convertStatus +'">'+convertStatus(option.convertStatus)+ '</span>'+
                    '<i class="es-icon es-icon-close01 delete" data-index="'+index+'"></i>'+
                '</li>';
            })
            return optionsStr;
        },
        setValue:function(value){
            if(!value){
                this.$dataShow.html('无字幕');
                this.trigger('valuechange',false);
                return;
            }
            this.value = value;
            this.trigger('valuechange',this.value);
        },
        getValue:function(){
            return this.value || { name:'无字幕'};
        },
        toggle:function(){
            this.open ? this.handleClose() : this.handleOpen();
        },
        handleOpen:function(){
            if(!this.options.length) return;
            this.open = true;
            this.$open.hide();
            this.$close.show();
            this.$showBox.addClass('active');
            this.$list.slideDown(200);
        },
        handleClose:function(){
            this.open = false;
            this.$close.hide();
            this.$open.show();
            this.$showBox.removeClass('active');
            this.$list.slideUp(200);
        },
        handleDelete:function(e){
            var el = e.target;
            $(el).parent().remove();
            this.trigger('deleteoption',this.options[$(el).data('index')]);
            this.options.splice($(el).data('index'),1);
            this.trigger('listchange',this.options);
            e.stopPropagation();
        },
        handleOptionEmpty: function(){
            this.value = '';
            this.trigger('valuechange',false);
        },
        on:function(event,fn){
            if(!this.eventManager[event]){
                this.eventManager[event] = [fn.bind(this)];
            }else{
                this.eventManager[event].push(fn.bind(this));
            }
        },
        trigger:function(event,data){
            if(this.eventManager[event]){
                this.eventManager[event].map(function(fn){
                    fn(data);
                });
            }
        },
        resetOptions:function(optionsArray){
            this.options = optionsArray;
            this.trigger('listchange',this.options);
        },
        addOption:function(option){
            if ( !option.convertStatus ){
                option.convertStatus = 'waiting';
            }
            this.options.push(option);
            this.trigger('listchange');
        }
    }
    module.exports = Select;
})