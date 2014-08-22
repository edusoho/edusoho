define(function(require, exports, module) {
	var Widget = require('widget');
	
	var ChunkUpload = Widget.extend({
		attrs: {
			fileQueue : [],
			uploadButton:'',
			tokenUrl: '/uploadfile/params',
			defaultBlockSize: 4 * 1024 * 1024,
			uploadUrl:'http://up.qiniu.com',
			defaultChunkSize: 1024 * 1024,
			tableArray: null,
			uploadTmps: 0
		},

		events: {
			putFailure: null
		},
		getFileSize: function(size) {
        	return (size / (1024 * 1024)).toFixed(2) + "MB";
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
			tr += "<td>"+this.getFileSize(file.size)+"</td>";
			tr += "<td id='file_"+index+"'>"+this.createProccess(file, index)+"</td>";
			tr += "</tr>";
			$("#fileList table tbody").prepend($(tr));
		},
		createProccess: function(file, index){
			return '<div class="progress">'
			+'<div id="progressbar'+index+'" class="progress-bar" role="progressbar" aria-valuenow="60" aria-valuemin="0" aria-valuemax="100" style="width: 0%;">'
			+'</div>'
			+'<div class="pLabel" id="progressbarLabel'+index+'"></div>'
			+'</div>';
		},
		showUploadButton: function(){
			var self = this;
			$("#btn_upload").prop("disabled", false);
			self.on('upload', self.onUpload);
			$("#btn_upload").on("click", function(e){
				self.trigger("upload", self.get("fileQueue").length-1);
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
		upload: function(file, fileIndex){
			var blockCount = this.blockCnt(file.size);
			var token = this.getToken();
			var blockCtxs = new Array();
			var fileScop = {
				fileIndex: fileIndex,
				file: file,
				startDate: new Date().getTime(),
				uploadedBytes: 0,
				blockCtxs: blockCtxs,
				token: token,

				defaultBlockSize: this.get("defaultBlockSize"),
				blockCount: blockCount,
				currentBlockIndex: 0,
				currentBlockSize: 0,

				currentChunkSize: 0,
				currentChunkIndex: 0,
				currentChunkOffsetInBlock: 0
			};

	        if (fileScop.file.size < fileScop.defaultBlockSize) {
                this.uploadSmallFile(fileScop.file);
            } else {
                this.uploadLargeFile(fileScop);
            }
		},
		blockCnt: function(fileSize) {
			var blockBits = 22;
    		var blockMask = (1 << blockBits) - 1;
	        return (fileSize + blockMask) >> blockBits;
	    },
	    getBlocksize: function(fsize, blkIdex) {
	    	var blkSize = this.get("defaultBlockSize");
	        var s = fsize > (blkIdex + 1) * blkSize ? blkSize : fsize - blkIdex * blkSize;
	        return s;
	    },
		uploadLargeFile: function(fileScop){
			this.uploadBlock(fileScop);
		},
		getChunkSize: function(offset, blkSize) {
			var defaultChunkSize = this.get("defaultChunkSize");
	        return defaultChunkSize < (blkSize - offset) ? defaultChunkSize : (blkSize - offset);
	    },
	    getChunk: function(f, start, size) {
	        if (f.slice) {
	            return f.slice(start, start + size);
	        }
	        if (f.webkitSlice) {
	            return f.webkitSlice(start, start + size);
	        }
	        return null;
	    },
		uploadBlock: function(fileScop){
			fileScop.currentBlockSize = this.getBlocksize(fileScop.file.size, fileScop.currentBlockIndex); 
			fileScop.currentChunkSize = this.getChunkSize(0, fileScop.currentBlockSize);
			
			var chunk = this.getChunk(fileScop.file, fileScop.uploadedBytes, fileScop.currentChunkSize);
			this.mkBlock(fileScop, chunk);
		},
		mkBlock: function(fileScop, chunk){
			console.log(fileScop);
			fileScop.currentBlockSize = this.getBlocksize(fileScop.file.size, fileScop.currentBlockIndex);
			var self=this;
			var xhr = new XMLHttpRequest();
            xhr.open('POST', this.get("uploadUrl") + "/mkblk/" + fileScop.currentBlockSize, true);
            xhr.setRequestHeader("Authorization", "UpToken " + fileScop.token.postParams.token);
			
            xhr.upload.addEventListener("progress", function(evt) {
                if (evt.lengthComputable) {
                    var nowDate = new Date().getTime();
                    var x = (evt.loaded) / 1024;
                    var y = (nowDate - fileScop.startDate) / 1000;
                    var uploadSpeed = (x / y);
                    var formatSpeed;
                    if (uploadSpeed > 1024) {
                        formatSpeed = (uploadSpeed / 1024).toFixed(2) + "Mb\/s";
                    } else {
                        formatSpeed = uploadSpeed.toFixed(2) + "Kb\/s";
                    }
                    var tmp = fileScop.uploadedBytes+evt.loaded;
                    var percentComplete = Math.round(100 * tmp / fileScop.file.size);
                    $("#progressbar"+fileScop.fileIndex).attr("style", "width: " + percentComplete + "%");
                    $("#progressbarLabel"+fileScop.fileIndex).text(percentComplete + "%" + ", 速度: " + formatSpeed);
                }
            }, false);

            xhr.onreadystatechange = function(response) {
                if (xhr.readyState == 4 && xhr.status == 200 && response != "") {
                	var blkRet = JSON.parse(xhr.responseText);

                	fileScop.uploadedBytes += fileScop.currentChunkSize;
        			fileScop.currentChunkIndex ++;
        			fileScop.currentChunkOffsetInBlock += fileScop.currentChunkSize;
					fileScop.currentChunkSize = self.getChunkSize(fileScop.currentChunkOffsetInBlock, fileScop.currentBlockSize);
					if(fileScop.currentChunkSize>0){
						var chunk = self.getChunk(fileScop.file, fileScop.uploadedBytes, fileScop.currentChunkSize);
                    	self.putChunk(fileScop, chunk, blkRet);
					}else{
						fileScop.blockCtxs[fileScop.currentBlockIndex] = blkRet.ctx;
						self.mkFile(fileScop);
					}
                }
            };
            xhr.send(chunk);

		},
		mkFile: function(fileScop){
			var self=this;
			var xhr = new XMLHttpRequest();
            xhr.open('POST', this.get("uploadUrl") + "/mkfile/" + fileScop.file.size, true);
            xhr.setRequestHeader("Authorization", "UpToken " + fileScop.token.postParams.token);
            var ctxs="";
            $.each(fileScop.blockCtxs, function(i,n){
            	if(i < (fileScop.blockCtxs.length-1))
            		ctxs += n+",";
            	else
            		ctxs += n;
            });
            xhr.onreadystatechange = function(response) {
                if (xhr.readyState == 4 && xhr.status == 200 && response != "") {
                	console.log(xhr.responseText);
                	fileScop.fileIndex--;
                	self.trigger("upload", fileScop.fileIndex);
                }
            }
            xhr.send(ctxs);
		},
		putChunk: function(fileScop, chunk, blkRet){
			console.log(fileScop);
			var uploadChunkSize = chunk.size;
			var self = this;
			var xhr = new XMLHttpRequest();
            xhr.open('POST', this.get("uploadUrl") + "/bput/" + blkRet.ctx + "/" + blkRet.offset, true);
            xhr.setRequestHeader("Authorization", "UpToken " + fileScop.token.postParams.token);
            xhr.upload.addEventListener("progress", function(evt) {
            	if (evt.lengthComputable) {
                    var nowDate = new Date().getTime();
                    var x = (evt.loaded) / 1024;
                    var y = (nowDate - fileScop.startDate) / 1000;
                    var uploadSpeed = (x / y);
                    var formatSpeed;
                    if (uploadSpeed > 1024) {
                        formatSpeed = (uploadSpeed / 1024).toFixed(2) + "Mb\/s";
                    } else {
                        formatSpeed = uploadSpeed.toFixed(2) + "Kb\/s";
                    }
                    var tmp = fileScop.uploadedBytes+evt.loaded;
                    var percentComplete = Math.round(100 * tmp / fileScop.file.size);
                    $("#progressbar"+fileScop.fileIndex).attr("style", "width: " + percentComplete + "%");
                    $("#progressbarLabel"+fileScop.fileIndex).text(percentComplete + "%" + ", 速度: " + formatSpeed);
                }
            },false);
            xhr.onreadystatechange = function(response) {
				if (xhr.readyState == 4 && xhr.status == 200 && response != "") {
					fileScop.uploadedBytes += fileScop.currentChunkSize;
					var blkRet=JSON.parse(xhr.responseText);
					fileScop.currentChunkIndex++;
					fileScop.currentChunkOffsetInBlock += fileScop.currentChunkSize;
					var chunkSize = self.getChunkSize(fileScop.currentChunkOffsetInBlock, fileScop.currentBlockSize);
					if(chunkSize > 0){
						fileScop.currentChunkSize = chunkSize;
						var chunk = self.getChunk(fileScop.file, fileScop.uploadedBytes, chunkSize);
						blkRet = self.putChunk(fileScop, chunk, blkRet);
					}else{
						fileScop.blockCtxs[fileScop.currentBlockIndex] = blkRet.ctx;
						fileScop.currentBlockIndex++;
						if(fileScop.currentBlockIndex<fileScop.blockCount){
							fileScop.blockSize = self.getBlocksize(fileScop.file.size, fileScop.currentBlockIndex); 
							fileScop.currentChunkIndex = 0;
							fileScop.currentChunkOffsetInBlock = 0;
							fileScop.currentChunkSize = self.getChunkSize(fileScop.currentChunkOffsetInBlock, fileScop.blockSize);
							var chunk = self.getChunk(fileScop.file, fileScop.uploadedBytes, fileScop.currentChunkSize);
							self.mkBlock(fileScop, chunk);
						}else{
							self.mkFile(fileScop);
						}
					}
				}
			};
			xhr.send(chunk);
		},
		getTableArray: function(){
			var strTable = "00000000 77073096 EE0E612C 990951BA 076DC419 706AF48F E963A535 9E6495A3 0EDB8832 79DCB8A4 E0D5E91E 97D2D988 09B64C2B 7EB17CBD E7B82D07 90BF1D91 1DB71064 6AB020F2 F3B97148 84BE41DE 1ADAD47D 6DDDE4EB F4D4B551 83D385C7 136C9856 646BA8C0 FD62F97A 8A65C9EC 14015C4F 63066CD9 FA0F3D63 8D080DF5 3B6E20C8 4C69105E D56041E4 A2677172 3C03E4D1 4B04D447 D20D85FD A50AB56B 35B5A8FA 42B2986C DBBBC9D6 ACBCF940 32D86CE3 45DF5C75 DCD60DCF ABD13D59 26D930AC 51DE003A C8D75180 BFD06116 21B4F4B5 56B3C423 CFBA9599 B8BDA50F 2802B89E 5F058808 C60CD9B2 B10BE924 2F6F7C87 58684C11 C1611DAB B6662D3D 76DC4190 01DB7106 98D220BC EFD5102A 71B18589 06B6B51F 9FBFE4A5 E8B8D433 7807C9A2 0F00F934 9609A88E E10E9818 7F6A0DBB 086D3D2D 91646C97 E6635C01 6B6B51F4 1C6C6162 856530D8 F262004E 6C0695ED 1B01A57B 8208F4C1 F50FC457 65B0D9C6 12B7E950 8BBEB8EA FCB9887C 62DD1DDF 15DA2D49 8CD37CF3 FBD44C65 4DB26158 3AB551CE A3BC0074 D4BB30E2 4ADFA541 3DD895D7 A4D1C46D D3D6F4FB 4369E96A 346ED9FC AD678846 DA60B8D0 44042D73 33031DE5 AA0A4C5F DD0D7CC9 5005713C 270241AA BE0B1010 C90C2086 5768B525 206F85B3 B966D409 CE61E49F 5EDEF90E 29D9C998 B0D09822 C7D7A8B4 59B33D17 2EB40D81 B7BD5C3B C0BA6CAD EDB88320 9ABFB3B6 03B6E20C 74B1D29A EAD54739 9DD277AF 04DB2615 73DC1683 E3630B12 94643B84 0D6D6A3E 7A6A5AA8 E40ECF0B 9309FF9D 0A00AE27 7D079EB1 F00F9344 8708A3D2 1E01F268 6906C2FE F762575D 806567CB 196C3671 6E6B06E7 FED41B76 89D32BE0 10DA7A5A 67DD4ACC F9B9DF6F 8EBEEFF9 17B7BE43 60B08ED5 D6D6A3E8 A1D1937E 38D8C2C4 4FDFF252 D1BB67F1 A6BC5767 3FB506DD 48B2364B D80D2BDA AF0A1B4C 36034AF6 41047A60 DF60EFC3 A867DF55 316E8EEF 4669BE79 CB61B38C BC66831A 256FD2A0 5268E236 CC0C7795 BB0B4703 220216B9 5505262F C5BA3BBE B2BD0B28 2BB45A92 5CB36A04 C2D7FFA7 B5D0CF31 2CD99E8B 5BDEAE1D 9B64C2B0 EC63F226 756AA39C 026D930A 9C0906A9 EB0E363F 72076785 05005713 95BF4A82 E2B87A14 7BB12BAE 0CB61B38 92D28E9B E5D5BE0D 7CDCEFB7 0BDBDF21 86D3D2D4 F1D4E242 68DDB3F8 1FDA836E 81BE16CD F6B9265B 6FB077E1 18B74777 88085AE6 FF0F6A70 66063BCA 11010B5C 8F659EFF F862AE69 616BFFD3 166CCF45 A00AE278 D70DD2EE 4E048354 3903B3C2 A7672661 D06016F7 4969474D 3E6E77DB AED16A4A D9D65ADC 40DF0B66 37D83BF0 A9BCAE53 DEBB9EC5 47B2CF7F 30B5FFE9 BDBDF21C CABAC28A 53B39330 24B4A3A6 BAD03605 CDD70693 54DE5729 23D967BF B3667A2E C4614AB8 5D681B02 2A6F2B94 B40BBE37 C30C8EA1 5A05DF1B 2D02EF8D".split(' ');

		    var table = new Array();
		    for (var i = 0; i < strTable.length; ++i) {
		        table[i] = parseInt("0x" + strTable[i]);
		    }
		    return table;
		},
		crc32: function(str, crc) {
	        if (crc == window.undefined) crc = 0;
	        var n = 0; //a number between 0 and 255
	        var x = 0; //an hex number
	        crc = crc ^ (-1);
	        for (var i = 0, iTop = str.length; i < iTop; i++) {
	            n = (crc ^ str.charCodeAt(i)) & 0xFF;
	            crc = (crc >>> 8) ^ this.get("tableArray")[n];
	        }
	        var number = crc ^ (-1);
	        if (number < 0) {
	            number = 0xFFFFFFFF + number + 1;
	        }
	        return number;

	    },
		uploadSmallFile: function(file) {
	        console.log(file.name);
	    },
		onUpload: function(fileIndex){
			var file = this.get("fileQueue").pop();
			if(file){
				this.upload(file, fileIndex);
			}
		},
		setup: function() {
			$("#btn_upload").prop("disabled", true);

			this.set("tableArray", this.getTableArray());
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

	JSON.stringifyArray = function(arr) {
	    var rest = "";
	    for (var i = 0; i < arr.length - 1; i++) {
	        rest += JSON.stringify(arr[i]);
	        rest += "+";
	    }
	    rest += JSON.stringify(arr[arr.length - 1]);
	    return rest;
	};

	JSON.parseArray = function(str) {
	    var rest = [];
	    strs = str.split("+");
	    for (var i = 0; i < strs.length; i++) {
	        rest.push(JSON.parse(strs[i]));
	    }
	    return rest;
	};

	module.exports = ChunkUpload;
});