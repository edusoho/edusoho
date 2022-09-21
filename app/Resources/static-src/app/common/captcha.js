import Emitter from 'component-emitter';
import Drag from 'app/common/drag';

export default class Captcha extends Emitter {
	constructor(prop) {
		super();
		this.params = {
			maskClass: "div.js-mask",
        	dragClass: "div.js-drag-jigsaw",
        	drag:{
        		limitType: "captcha",
				bar: '#drag-btn',
				target: '.js-jigsaw',
        	},
        	type: "captcha"
		}
		this.params = Object.assign(this.params, prop);
		this.drag = null;
		this.init();
	}

	init() {
		let _this = this;
		this.drag = $(this.params.drag.bar).length ? new Drag($(this.params.drag.bar), $(this.params.drag.target), {limitType: this.params.drag.limitType}) : null;
		this.initEvent();
	}

	initEvent() {
		let _this = this;
		$(this.params.maskClass).click(function(){
	      _this.hideDrag();
	    })
		if(this.drag != null){
		    this.drag.on("success", function(data){
		    	_this.emit('success',{type:_this.params.type, token: data.token });
		    })
	    }
	}

	showDrag(){
		var width = document.documentElement.clientWidth;
		var height = document.documentElement.clientHeight;
		$(this.params.dragClass).css({top: (height-219)/2+"px",left:(width-338)/2+"px"});
    	$(this.params.maskClass+","+this.params.dragClass).show();
  	}

	hideDrag(){
		$(this.params.maskClass+","+this.params.dragClass).hide();
		this.drag.initDragCaptcha();
	}

	setType(type){
		this.params.type = type;
	}
}