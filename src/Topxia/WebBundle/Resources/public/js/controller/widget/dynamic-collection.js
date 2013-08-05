define(function(require, exports, module) {

    var Widget = require('widget');
    var SortableUI = require('jquery.ui');
    var DynamicCollection = Widget.extend({
        events: {
            'click [data-role=item-add]' : 'addItem',
            'keydown [data-role=item-input]' : 'addItemWithEnter',
            'click [data-role=item-delete]' : 'deleteItem',
            'mouseover [data-role=list]' : 'moveItem',
        },

        setup: function() {
          this.itemCount = this.element.data('listSize');
        },

        moveItem: function(){
          $("[data-role=list]").sortable().disableSelection();
          $( "[data-role=list]" ).sortable({
            stop: function(event, ui) {
              $("[data-role=list] li input").each(function(index, domEle){
                  var name = $(this).attr("name");
                  var reg = /([\d])/;
                  var t = reg.exec(name);
                  var result = name.replace(t[0], index);
                  $(this).attr('name', result);
              })
            }      
          });
        },

        addItem: function() {

          var template = this.$('[data-role=list-item-template]').html().replace(/__name__/g, this.itemCount),
              itemText = this.$('[data-role=item-input]').val(),
              $template = $(template);

          if (itemText.length === 0) {
              return ;
          }

          $template.find('[data-role=item-text]').html(itemText); 
          $template.find('[data-role=item-value]').val(itemText).removeAttr('disabled');

          this.$('[data-role=list]').append($template);

          this.$('[data-role=item-input]').val('');

          this.itemCount++;
        },

        addItemWithEnter: function(e) {
          if (e.which == 13) {
              e.preventDefault();
              this.addItem();
          }
        },

        deleteItem: function(e) {
          $(e.currentTarget).parents('li').remove();
        }
    });

    module.exports = DynamicCollection;
});