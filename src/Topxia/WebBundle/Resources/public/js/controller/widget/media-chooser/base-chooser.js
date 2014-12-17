define(function(require,exports,module){
        var Widget = require('widget');

        var BaseChooser = Widget.extend({
            attrs: {
                type : null
            },

            open: function ()
            {
                this.hidePlaceholder();
                this.showPanel();
            },

            searchItems: function ($paras)
            {
                $btn = $("[data-role=search-"+this.get('type')+"s-btn]");
                var self = this;
                $.get($btn.data('url'),$paras,function(items){
                    self._commonSearchItems($btn,items);
                });

            },

            showPlaceholder: function ()
            {
                this.element.find('[data-role='+this.get('type')+'-placeholder]').parent().removeClass('hide');
            },

            hidePlaceholder: function ()
            {
                this.element.find('[data-role='+this.get('type')+'-placeholder]').parent().addClass('hide');
            },

            showModule: function ()
            {
                this.element.removeClass('hide');
            },

            hideModule: function ()
            {
                this.element.addClass('hide');
            },

            showPanel: function ()
            {
                this.element.find('.'+this.get('type')+'-chooser').removeClass('hide');
            },

            hidePanel: function ()
            {
                this.element.find('.'+this.get('type')+'-chooser').addClass('hide');
            },

            _getUrlRule: function ()
            {
                return /^(http|https):\/\/(\w+:{0,1}\w*@)?(\S+)(:[0-9]+)?(\/|\/([\w#!:.?+=&%@!\-\/]))?$/;
            },
            _commonSearchItems: function ($element,items)
            {
                var html = "";

                $element.text('搜索');
                var self = this;
                $.each(items,function(index,item){
                    var title = item.title;
                    if (self.get('type') == 'testpaper') {
                        title = item.name;
                    };
                    html += "<tr style=\"cursor:pointer;\" data-role=\"search-"+self.get('type')+"-item\" data-id=\""+item.id+"\"><td>"+title+"</td></tr>"
                });
                $('.search-'+this.get('type')+'-result-table').find('tbody').html(html);
                $('[data-role=search-'+this.get('type')+'-item]').on('click',function(){
                    $('[data-role='+self.get('type')+'-placeholder]').attr("data-id",$(this).data('id'));
                    $('[data-role='+self.get('type')+'-placeholder]').html($(this).find('td').text());
                        self.hidePanel();
                        self.showPlaceholder();
                });
            }
        });

        module.exports = BaseChooser;
    });