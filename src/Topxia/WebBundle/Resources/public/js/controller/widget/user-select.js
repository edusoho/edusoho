define(function(require, exports, module) {
    var Widget = require('widget');
    require('jquery.select2-css');
    require('jquery.select2');

    var userSelect = Widget.extend({
        attrs: {
        	placeholder: '请选择用户'
        },
        events: {
        },
        setup: function() {
        	this.url = this.element.data('url');
        	this.element.select2({
        	       ajax: {
        	           url: this.url + '#',
        	           dataType: 'json',
        	           quietMillis: 100,
        	           data: function(term, page) {
        	               return {
        	                   q: term,
        	                   page_limit: 20
        	               };
        	           },
        	           results: function(data) {
        	               var results = [];
        	               $.each(data, function(index, item) {
        	                   results.push({
        	                       id: item.id,
        	                       name: item.name
        	                   });
        	               });
        	               return {
        	                   results: results
        	               };
        	           }
        	       },
        	       initSelection: function(element, callback) {
        	           var data = [];
        	           data['id'] = element.data('id');
        	           data['name'] = element.data('name');
        	           element.val(element.data('id'));
        	           callback(data);
        	       },
        	       formatSelection: function(item) {
        	           return item.name;
        	       },
        	       formatResult: function(item) {
        	           return item.name;
        	       },
        	       width: 'off',
        	       multiple: false,
        	       placeholder: this.get('placeholder'),
        	       createSearchChoice: function() {
        	           return null;
        	       }
        	   });
        }
    });
    module.exports = userSelect;

});