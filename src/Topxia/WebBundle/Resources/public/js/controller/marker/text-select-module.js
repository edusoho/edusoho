define(function(require,exports,module){
    var Select = {
        init:function(id){
            if(!$('#'+id).length){
                throw new Error('id 不存在');
            }
            this.$dataDom = undefined;
            this.$parentDom = undefined;
            this.value = {};
            this.eventManager = {};
            this.$options = [];
            this.$dataDom = $('#'+id);
            this.$dataDom.hide();
            this.initParent();
            this.initEvent();
        },
        initParent:function(){
            var _self = this;
            $documentFragment = $(document.createDocumentFragment());
            this.$dataDom.find('option').each(function(){
                _self.$options.push($(this));
            });
            $documentFragment.append(this.templete());
            this.$dataDom.before($documentFragment);
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
                var label = $(this).find('.value').html();
                var src = $(this).find('.value').attr('src');
                _self.setValue({label:label,src:src});
                _self.handleClose();
            })
            this.$showBox.on('click',this.toggle.bind(this));
            this.on('valuechange',function(){
                this.$dataDom.val(this.value);
                this.$dataShow.html(this.value.label);
                this.$dataShow.attr('title',this.value.label);
            });
            this.on('listchange',function(){
                this.$options.map(function($option){
                    console.log($option.html())
                })
                this.$list.html(this.getOptionsStr());
                this.setValue(this.getDefaultOption())
            })
        },
        templete:function(){
            return ''+
                '<div class="track-select-parent">'+
                    '<div class="track-select-show">'+
                        '<div class="data-show" title="'+ this.getDefaultOption() +'">'+ this.getDefaultOption() +'</div>'+
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
        getDefaultOption() {
            if(this.$options.length){
                return this.$options[0].html();
            }else{
                this.open ? this.handleClose() : '';
                return '无字幕';
            }
        },
        getOptionsStr:function(){
            var optionsStr = '';
            this.$options.map(function($option,index){
                optionsStr += '<li class="select-item"><div class="value" title="'+ $option.html() +'" src="'+$option.val()+'">'+$option.html()+'</div><i class="es-icon es-icon-close01 delete" data-index="'+index+'"></i></li>';
            })
            return optionsStr;
        },
        setValue:function(value){
            if(this.value.label !== value.label){
                this.value = value;
                this.trigger('valuechange',this.value);
            }
        },
        getValue:function(){
            return this.value;
        },
        toggle:function(){
            this.open ? this.handleClose() : this.handleOpen();
        },
        handleOpen:function(){
            if(!this.$options.length) return;
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
            this.$options.splice($(el).data('index'),1);
            this.trigger('listchange',this.$options);
            e.stopPropagation();
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
        addOption:function(value){
            optionStr = '<option value="'+value.src+'">'+value.label+'</option>';
            this.$options.push($(optionStr));
            this.trigger('listchange',this.$options);
        }
    }
    module.exports = Select;
})