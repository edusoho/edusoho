(function() {
	CKEDITOR.dialog.add("kityformula",function(editor) {
		var html='<div id="kfEditorContainer" class="kf-editor" style="width: 750px; height: 502px;"></div>';
		var isIE=!-[1,];
		if (isIE) {
			html='<div class="formulaOld" style="width:750px;height:350px;"><div class="tips" style="color:red;line-height:40px;">请升级您的浏览器到IE9版本及以上版本，或使用chrome浏览器使用可视化公式编辑器</div><textarea id="oldFormula" style="width:100%;height:240px;border:1px solid #ccc;"></textarea></div>';
		}
		return {
			title:'公式编辑器',
			contents: [{
				id: 'kityformula',
				label: '公式编辑器',
				title: '公式编辑器',
				expand: true,
				padding: 0,
				elements: [{
					type: "html",
					html:html
				}]
			}],
			onHide: function(){
				$("#cke_"+editor.name).data("source","");
			},
			onShow: function() {
				var source=$("#cke_"+editor.name).data("source");
				if(isIE){
					$("#oldFormula").val(source);
					return false;
				}
				if(!source){
					source='\\placeholder';
				}
				if(window.kfe){
					window.kfe.execCommand( "render", source);
				}
			},
			onOk: function() {
				var source;
				if(isIE){
					source=$("#oldFormula").val();
				}else{
					source=kfe.execCommand( "get.source" );
					if ($.trim(source) == "\\placeholder"){
						return;
					}
				}
				var $imgUrl = 'http://formula.edusoho.net/cgi-bin/mimetex.cgi?'+source;
				$.post($('#'+editor.name).data('imageDownloadUrl')+'&url='+$imgUrl, function(result){
					var insertHtml='<img kityformula="true" src="'+result+'" alt="'+source+'">';
					editor.insertHtml(insertHtml);
				});
			},
			onLoad:function(){
				if(!isIE){
					var ready=function(){}
					ready.prototype={
						js:editor.kityFormuaPath+"libs/js/kityformula.js",
						css:editor.kityFormuaPath + 'libs/css/base.css',
						loadCSS:function(url,fn){
							var link = document.createElement('link');
							link.type = 'text/css';
							link.rel = 'stylesheet';
							var isLoad = false;
							link.onload = function() {
								if(!isLoad){
									isLoad = true;
									fn&&fn();
								}
							};
							setTimeout(function(){
								if(!isLoad){
									isLoad = true;
									fn&&fn();
								}
							},1000);

							link.href = url;
							document.getElementsByTagName('head')[0].appendChild(link);
						},
						loadJS:function(url,fn){
							var script = document.createElement('script');
							script.type = 'text/javascript';
							script.charset = 'UTF-8';

							if (script.readyState){  //IE
								script.onreadystatechange = function(){
									if (script.readyState == "loaded" || script.readyState == "complete"){
										script.onreadystatechange = null;
										fn&&fn();
									}
								};
							} else {  //Others
								script.onload = function(){
									fn&&fn();
								};
							}

							script.src = url;
							document.getElementsByTagName('head')[0].appendChild(script);
						},
						initKityformula:function(){
							var factory = kf.EditorFactory.create($("#kfEditorContainer")[0], {
								render: {
									fontsize: 40
								},
								resource: {
									path: editor.kityFormuaPath+"libs/fonts/"
								},
								toolbarPath:editor.kityFormuaPath+'libs/images/toolbar/'
							});
							factory.ready( function ( KFEditor ) {
								var source=$("#cke_"+editor.name).data("source");
								if(!source){
									source='\\placeholder';
								}
								// this指向KFEditor
								KFEditor.execCommand( "render",source);
								KFEditor.execCommand( "focus" );
								window.kfe = KFEditor;
							});
						},
						init:function(){
							var self=this;
							self.loadCSS(self.css,function(){
								self.loadJS(self.js,function(){
									self.initKityformula();
								});
							});
						}
					}

					var Ready = new ready();
					Ready.init();
				}
			}
		};
	})
})();
