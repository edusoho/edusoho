define(function(require, exports, module) {
	var Widget = require('widget');
	
	var ChunkUpload = Widget.extend({
		attrs: {
			fileQueue : [],
			uploadButton:''
		},
		mkblk: function(){

		},
		uploadChunk: function(){

		},
		mkfile: function(){

		},
		
		onChanged: function(files){
			var globalFiles = this.get("fileQueue");

			for (var i = 0; i < files.length; i++) {
                globalFiles.push(files[i]);
                this.addFileItem(files[i]);
            }
            this.showUploadButton();
            this.set("fileQueue", globalFiles);
		},
		addFileItem: function(file){
			var tr = "<tr>";
			tr += "<td>"+file.name+"</td>";
			tr += "<td>"+file.size+"</td>";
			tr += "<td>"+this.createProccess(file)+"</td>";
			tr += "</tr>";
			$("#fileList table tbody").append($(tr));
		},
		createProccess: function(file){
			return '<div class="progress">'
			+'<div id="progressbar0" class="progress-bar" role="progressbar" aria-valuenow="60" aria-valuemin="0" aria-valuemax="100" style="width: 0%;">'
			+'</div>'
			+'<div class="pLabel" id="progressbarLabel0"></div>'
			+'</div>';
		},
		showUploadButton: function(){
			console.log(1111);
			var self = this;
			$("#btn_upload").show();
			self.on('upload', self.onUpload);
			$("#btn_upload").on("click", function(e){
				console.log('aaa');
				self.trigger("upload", this);
			});
		},
		preUpload: function(){

		},
		onUpload: function(element){
			console.log("uploading");
		},
		setup: function() {
			var self = this;
			self.on('change', self.onChanged);
			self.element.on("change", function(e){
				self.trigger("change", this.files);
			});
			
		}
	});

	module.exports = ChunkUpload;
});