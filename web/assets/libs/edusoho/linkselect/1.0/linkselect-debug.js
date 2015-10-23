/**
 * LinkSelect是从Kissy的Proince组件转换到了Arale的组件
 *
 * @author Wellming Li <wellming.li@gmail.com>
 * 
 * Thanks:
 * 常胤<changyin@taobao.com>
 * https://github.com/kissyteam/kissy-gallery/tree/master/gallery/province
 *
 */
define(function(require, exports, module) {
    var $ = require('$');
    var Widget = require('arale/widget/1.0.3/widget');;

    var LinkSelect = Widget.extend({
        attrs: {
            autoRender:true,
            defval: {
                text: "请选择", val: ""
            },
            rootid: 0,
            data: []
        },
        events: {
            'change select[data-role=item]': '_changeHandler',
        },
        setup: function() {
            //select对象管理类
            var selectManager = this.selectManager = new SelectManager();
            
            this.element.find('select[data-role=item]').each(function(index, item){
                var $item = $(item);
                if($item[0]){
                    var objsel = selectManager.add($item[0], index);
                }
            });

            if(this.get('autoRender') == true) {
                var sel = selectManager.get(0);
                if(!sel) {
                    return;
                }
                this._render(sel, this.get('rootid'));
                if(selectManager.get(1)){
                    this.focus(sel[0], this.getValue(sel[0]));
                }
            }
        },
        getValue: function(el) {
            //return (el && el.options)?(el.options[el.selectedIndex]?el.options[el.selectedIndex].value:""):"";
            if(el && el.options && el.options.length>0 && el.selectedIndex>0){
                return el.options[el.selectedIndex].value;
            }else{
                return null;
            }
        },
        _forward: function(el,val) {
            var backEl = this.selectManager.get(el);
            if(backEl){
                var thisEl = this.selectManager.get(backEl.index+1);
                if(thisEl) {
                    this._render(thisEl,val);
                }else{
                    return;
                }
                this._forward(thisEl[0], this.getValue(thisEl[0]));
            }else{
                return;
            }
        },
        _backward: function(el,val) {
            var data = this.get('data'),
                pid = data[val] ? data[val][1] : null,
                forwardEl = this.selectManager.get(el);

            if(forwardEl && pid){
                var thisEl = this.selectManager.get(forwardEl.index-1),
                    ppid = data[pid] ? data[pid][1] : null;
                
                if(thisEl && pid) {
                    this._render(thisEl,ppid,pid);
                }else{
                    return;
                }
                
                this._backward(thisEl[0], this.getValue(thisEl[0]));
                
                
            }else{
                return;
            }

        },
        _changeHandler: function(ev) {
            var tg = ev.target,
                thisEl = this.selectManager.get(tg);
                forwardEl = this.selectManager.get(thisEl.index+1);

            this._forward(tg, $(tg).val());

            var $valueInput = this.element.find('[data-role=value]');
            if (!forwardEl || $(forwardEl[0]).find('option').length == 0) {
            	$valueInput.val($(tg).val()).trigger('change');
            } else {
            	$valueInput.val('');
            }
        },
        _getData: function(objsel,pid) {
            var options = [];
            
            if(pid===-1  || pid==="" || !objsel.data)
                return;
            
            //数据已经初始化
            if(objsel.data[pid]){
                return objsel.data[pid];
            }
            
            //数据没有初始化
            var tdata = objsel.data[pid] = [];
            
            //本地数据
            $.each(this.get('data'), function(key, item){
                // if(item.parent == pid){
                    // tdata.push([key,item.text]);
                    //delete self.data[key];
                    // options.push([key,item.text]);
                // }
                if(item[1]==pid){
                    tdata.push([key,item[0]]);
                    //delete self.data[key];
                    options.push([key,item[0]]);
                }
            });
            
            return options;
        },
        _render: function(objsel, pid, val) {
            var sel = objsel["0"],
                options = this._getData(objsel,pid) || [];
            
            if(!sel) {
            	return;
            } 
            	
            //clear
            sel.options.length=0;
            
            //default tip
            if(options && options.length>0 && this.get('defval')) {
                sel.options[sel.options.length] = new Option(this.get('defval').text,this.get('defval').val);
            }
                
            //add all option
            $.each(options,function(index, item) {
                    //sel.add(new Option(item[1],item[0]),null); 
                    sel.options[sel.options.length] = new Option(item[1],item[0]); 
            });
            
            if(val) {
                sel.value=val;
            }

        },
        focus: function(sel,val) {
            var thisEl = this.selectManager.get(sel),
                data = this.get('data'),
                pid = data[val] ? data[val][1] : null;
            if(!pid) {
                return;
            }
            
            this._render(thisEl,pid,val);
            this._forward(sel,val);
            this._backward(sel,val);
        }

    });

    module.exports = LinkSelect;

    function SelectManager(){
        var store = [];
        this.add = function(item,index) {
            var l = store.length;
            store[l] = {
                "index": index,
                "0": item,
                "data": {}
            };
            return store[l];
        };
        this.get = function(sel) {
            var obj = null;
            if($.isNumeric(sel) && sel<=store.length) {
                return store[sel];
            }
            $.each(store, function(index, item) {
                if(item["0"]==sel){
                    obj = item;
                    return false;
                }
            });
            return obj;
        };
    }

});