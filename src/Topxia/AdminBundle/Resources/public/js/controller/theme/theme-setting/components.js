define(function(require, exports, module) {
    require('jquery.sortable');
    exports.run = function() {
      var $themeEditContent = $('#theme-edit-content'), $componets = $('.js-theme-component');
      var componentSetting = {}, $modal = $('#modal');
      $('.js-componet-setting').each(function(){
        var $this = $(this);
        var blockKey = $this.data('key'), config = $.parseJSON($this.text());
        config.blockKey = blockKey;
        componentSetting[blockKey] = config;
      });
      
      var $list = $(".module-item-list").sortable({
        distance: 20,
        itemSelector: '.theme-edit-item',
        onDrop: function (item, container, _super) {
          _super(item, container);
          $themeEditContent.trigger('save_config', getConfig());
        },
      });

      var getConfig = function(){
        var setting = {};
        $componets.each(function(){
          var $this = $(this);
          var $actives = $this.find('input[type=checkbox]:checked'), blockKey = $this.attr('id');
          $actives.each(function(){
            var key = $(this).data('componentId');
            if (undefined == setting[blockKey]) {
                setting[blockKey] = {};
            }
            
            setting[blockKey][key] = componentSetting[blockKey][key];
          });
        });
       
        return {
          blocks: setting
        };
      };
      
      $componets.on("click", '.check-block', function(event){
        var $this = $(this);
        if ($this.prop('checked') == true) {
            $this.parents('li').find('.item-edit-btn,.item-set-btn').show();
        } else {
            $this.closest('li').find('.item-edit-btn,.item-set-btn').hide();
        }
        $themeEditContent.trigger('save_config', getConfig());
      });

      $componets.on('click', '.item-edit-btn', function(event){
        var $this = $(this);
        var key = $(this).closest('li').find('.check-block').data('componentId'),
        blockKey = $(this).closest('.js-theme-component').attr('id'),
        url = $this.data('url'),
        config = componentSetting[blockKey][key];
        config.blockKey = blockKey;

        $.get(url, {config: config}, function(html){
          $modal.html(html)
          $modal.modal('show');
        });
      })

      $themeEditContent.on("save_part_config", function(event, data){
        componentSetting[data.blockKey][data.id] = $.extend(componentSetting[data.blockKey][data.id], data);
        $("#"+ data.id).find('>div').eq(1).text(componentSetting[data.blockKey][data.id].title);

        $themeEditContent.trigger('save_config', getConfig());
      });

    }
});