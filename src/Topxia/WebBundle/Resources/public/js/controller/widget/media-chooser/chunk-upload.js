define(function(require, exports, module) {
	var Widget = require('widget');
	
	var ChunkUpload = Widget.extend({
		attrs: {
			fileQueue : [],
			uploadButton:'',
			tokenUrl: '/uploadfile/params',
			blkSize: 4 * 1024 * 1024
		},

		events: {
			putFailure: null
		},

		onChanged: function(files){
			var globalFiles = this.get("fileQueue");

			for (var i = 0; i < files.length; i++) {
                globalFiles.push(files[i]);
                this.addFileItem(files[i], i);
            }
            this.showUploadButton();
            this.set("fileQueue", globalFiles);
		},
		addFileItem: function(file, index){
			var tr = "<tr>";
			tr += "<td>"+file.name+"</td>";
			tr += "<td>"+file.size+"</td>";
			tr += "<td id='file_"+index+"'>"+this.createProccess(file)+"</td>";
			tr += "</tr>";
			$("#fileList table tbody").prepend($(tr));
		},
		createProccess: function(file){
			return '<div class="progress">'
			+'<div id="progressbar0" class="progress-bar" role="progressbar" aria-valuenow="60" aria-valuemin="0" aria-valuemax="100" style="width: 0%;">'
			+'</div>'
			+'<div class="pLabel" id="progressbarLabel0"></div>'
			+'</div>';
		},
		showUploadButton: function(){
			var self = this;
			$("#btn_upload").prop("disabled", false);
			self.on('upload', self.onUpload);
			$("#btn_upload").on("click", function(e){
				self.trigger("upload", this);
			});
		},
		preUpload: function(){

		},
		getToken: function(){
			var params = {
				storage : "cloud",
				targetType : "courselesson",
				targetId: "26",
				videoQuality: "low",
				audioQuality: "low",
				convertor: "HLSEncryptedVideo"
			};
			var token;
			$.ajax({
				url: this.get("tokenUrl"), 
				data: params, 
				async: false,
				success: function(response){
					token = response;
				}
			});
			return token;
		},
		upload: function(file){
	        var token = this.getToken();
	        console.log(file.size);
	        if (file.size < this.get("blkSize")) {
                this.uploadSmallFile(file);
            } else {
                this.uploadLargeFile(file);
            }
		},
		blockCnt: function(fileSize) {
			var blockBits = 22;
    		var blockMask = (1 << blockBits) - 1;
	        return (fileSize + blockMask) >> blockBits;
	    },
	    getBlocksize: function(fsize, blkIdex) {
	    	var blkSize = this.get("blkSize");
	        var s = fsize > (blkIdex + 1) * blkSize ? blkSize : fsize - blkIdex * blkSize;
	        return s;
	    };
		uploadLargeFile: function(file){
			var blockCount = this.blockCnt(file.size));
			for(var i=0; i<blockCount; i++){
				var blockSize = this.getBlocksize(file.size, i);
				this.putBlock(file, i, firstBlockSize, blockCount);
			}
		},
		putBlock: function(file, blkIdex, blockSize, blkCnt){
			this.mkBlock();
		},
		uploadSmallFile: function(file) {
	        console.log(file.name);
	    },
		onUpload: function(element){
			var index=0;
			while(this.get("fileQueue").length>0) {
				var file = this.get("fileQueue").pop();
				this.upload(file);
			}
		},
		setup: function() {
			$("#btn_upload").prop("disabled", true);
			var self = this;
			self.on('change', self.onChanged);
			self.element.on("change", function(e){
				self.trigger("change", this.files);
			});
			
		}
	});
	
	var Cookie = {
	    set: function(name, value) {
	        value = JSON.stringify(value);
	        var exp = new Date();
	        exp.setTime(exp.getTime() + 30 * 24 * 3600);
	        document.cookie = name + "=" + escape(value) + ";expires=" + exp.toGMTString();
	    },
	    get: function(name) {
	        var arr = document.cookie.match(new RegExp("(^| )" + name + "=([^;]*)(;|$)"));
	        if (arr != null) return JSON.parse(unescape(arr[2]));
	        return null;
	    },
	    del: function(name) {
	        var exp = new Date();
	        exp.setTime(exp.getTime() - 1);
	        var cval = this.get(name);
	        if (cval != null) document.cookie = name + "=" + cval + ";expires=" + exp.toGMTString();
	    }
	};

	module.exports = ChunkUpload;
});