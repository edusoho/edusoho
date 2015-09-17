define(function(require, exports, module) {
    require('webuploader2');
    var store = require('store');
    var filesize = require('filesize');
    var Widget = require('widget');

    var hookRegisted = false;

    var BatchUploader = Widget.extend({

        uploader: null,

        attrs: {
            initUrl: null,
            finishUrl: null,
            uploadUrl: null,
            accept: null,
            process: 'none',
            uploadToken: null
        },

        setup: function() {
            this._initUI();
            var accept = {};
            accept.title = '文件';
            accept.extensions = this.get('accept')['extensions'].join(',');
            accept.mimeTypes = this.get('accept')['mimeTypes'].join(',');

            var defaults = {

                dnd: this.element.find('.balloon-uploader-body'),
                accept: accept,

                // 不压缩image
                resize: false,

                // swf文件路径
                swf: 'Uploader.swf',

                // 选择文件的按钮。可选。
                // 内部根据当前运行是创建，可能是input元素，也可能是flash.
                pick: this.element.find('.file-pick-btn') ,
                chunked: true,
                chunkSize: 1024000,
                chunkRetry: 2,
                threads: 1,
                formData: {

                }
            };

            this._initUploaderHook();
            var uploader = this.uploader = WebUploader.create(defaults);
            this._registerUploaderEvent(uploader);

            this.element.find('.start-upload-btn').on('click', function(){
                uploader.upload();
            });
        },

        destroy: function() {
            if (this.uploader) {
                this.uploader.destroy();
            }
            BatchUploader.superclass.destroy.call(this);
        },

        _initUI: function() {
            var html = '';
            html += '<div class="balloon-uploader-heading">上传文件</div>';
            html += '<div class="balloon-uploader-body">';
            html += '  <div class="balloon-nofile">请将文件拖到这里，或点击添加文件按钮</div>';
            html += '  <div class="balloon-filelist">';
            html += '    <div class="balloon-filelist-heading">';
            html += '    <div class="file-name">文件名</div>';
            html += '    <div class="file-size">大小</div>';
            html += '    <div class="file-status">状态</div>';
            html += '  </div>';
            html += '  <ul></ul>';
            html += '</div>';
            html += '<div class="balloon-uploader-footer">';
            html += '  <div class="file-pick-btn"><i class="glyphicon glyphicon-plus"></i> 添加文件</div>';
            html += '</div>';

            this.element.addClass('balloon-uploader');
            this.element.html(html);
        },

        _registerUploaderEvent: function(uploader) {
            var self = this;
            var $uploader = this.element;
            // 当有文件添加进来的时候
            uploader.on('fileQueued', function(file) {
                $uploader.find('.balloon-nofile').remove();
                var $list =$uploader.find('.balloon-filelist ul');
                $list.append(
                    '<li id="' + file.id + '">' +
                    '  <div class="file-name">' + file.name + '</div>' +
                    '  <div class="file-size">' + filesize(file.size) + '</div>' +
                    '  <div class="file-status">待上传</div>' +
                    '  <div class="file-progress"><div class="file-progress-bar" style="width: 0%;"></div></div>' +
                    '</li>'
                );

                self.trigger('file.queued', file);
            });

            // 文件上传过程中创建进度条实时显示。
            uploader.on('uploadProgress', function(file, percentage) {
                var $li = $('#' + file.id);
                percentage = (percentage * 100).toFixed(2) + '%';
                $li.find('.file-status').html(percentage);
                $li.find('.file-progress-bar').css('width', percentage);
            });

            uploader.on('uploadSuccess', function(file) {
                var $li = $('#' + file.id);
                $li.find('.file-status').html('已上传');
                $li.find('.file-progress-bar').css('width', '0%');
            });

            uploader.on('beforeFileQueued', function(file) {
                file.uploaderWidget = self;
            });

            uploader.on('uploadComplete', function(file) {
                var key = 'file_' + file.globalId + '_' + file.hash;
                store.remove('file_' + file.hash);
            });

            uploader.on('uploadAccept', function(object, ret) {
                var key = 'file_' + object.file.globalId + '_' + object.file.hash;
                store.set(key, object.chunk);
            });

            uploader.on('uploadStart', function(file) {
            });

            uploader.on('uploadBeforeSend', function(object, data, headers) {
                data.file_gid = object.file.gid;
                data.chunk_number = object.chunk +1;
                headers['Upload-Token'] = self.get('uploadToken');
            });
        },

        _getProcessParams: function(file) {
            var extOutputs = {
                mp4: 'HLSEncryptedVideo',
                avi: 'HLSEncryptedVideo',
                flv: 'HLSEncryptedVideo',
                f4v: 'HLSEncryptedVideo',
                wmv: 'HLSEncryptedVideo',
                mov: 'HLSEncryptedVideo',
                rmvb: 'HLSEncryptedVideo',
                mkv: 'HLSEncryptedVideo',
                doc: 'document',
                docx: 'document',
                pdf: 'document',
                ppt: 'ppt',
                pptx: 'ppt',
                mp3: 'audio'
            };
            file

            var paramsDefault = {
                'HLSEncryptedVideo' : {videoQuality: 'normal', audioQuality: 'normal'},
                'document' : {},
                'ppt' : {},
                'audio' : {}
            }

            var params = {};
            if ((this.get('process') == 'auto') && extOutputs[file.ext]) {
                params = paramsDefault[extOutputs[file.ext]];
                params.output = extOutputs[file.ext];
            } 

            return params;
        },

        _initUploaderHook: function() {
            if (hookRegisted) {
                return ;
            } else {
            }

            hookRegisted = true;

            WebUploader.Uploader.register({
                'before-send-file': 'preupload',
                'before-send' : 'checkchunk',
                'after-send-file': 'finishupload',
            }, {
                preupload: function(file) {
                    var deferred = WebUploader.Deferred();

                    file.uploaderWidget._makeFileHash(file).done(function(hash) {
                        file.hash = hash;
                        var params = {
                            fileName: file.name,
                            fileSize: file.size,
                            hash: hash,
                            processParams: file.uploaderWidget._getProcessParams(file)
                        }

                        $.support.cors = true;

                        $.post(file.uploaderWidget.get('initUrl'), params, function(response) {
                            file.gid = response.globalId;
                            file.globalId = response.globalId;
                            file.outerId = response.outerId;

                            file.uploaderWidget.set('uploadToken', response.postData.token);
                            file.uploaderWidget.set('uploadUrl', response.uploadUrl);
                            file.uploaderWidget.uploader.option('server', response.uploadUrl + '/chunks');

                            var startUrl = response.uploadUrl + '/chunks/start';
                            var postData = {file_gid:file.globalId, file_size: file.size, file_name:file.name};

                            $.ajax(startUrl, {
                                type: 'POST',
                                data: postData,
                                dataType: 'json',
                                headers: {
                                    'Upload-Token': response.postData.token
                                },
                                success: function() {
                                    deferred.resolve();
                                }
                            });

                        }, 'json');

                    });

                    return deferred.promise();
                },

                checkchunk: function(block) {
                    var deferred = WebUploader.Deferred();

                    var key = 'file_' + block.file.globalId + '_' + block.file.hash;

                    var resumedChunk = store.get(key);

                    if (resumedChunk === undefined) {
                        block.file.startUploading = true;
                    }

                    if (!block.file.startUploading && block.chunk <= resumedChunk) {
                        deferred.reject();
                    } else {
                        block.file.startUploading = true;
                    }

                    deferred.resolve();

                    return deferred.promise();
                },

                finishupload: function(file) {
                    store.remove('file_' + file.hash);
                    var deferred = WebUploader.Deferred();

                    var xhr = $.ajax(file.uploaderWidget.get('uploadUrl') + '/chunks/finish', {
                        type: 'POST',
                        data: {file_gid:file.gid},
                        dataType: 'json',
                        headers: {
                            'Upload-Token': file.uploaderWidget.get('uploadToken')
                        }
                    });

                    xhr.done(function( data, textStatus, xhr ) {
                        $.post(file.uploaderWidget.get('finishUrl'), data, function() {
                            deferred.resolve();
                            file.uploaderWidget.trigger('file.uploaded', file, data);
                        });
                    });

                    return deferred.promise();
                }

            });

        },

        _makeFileHash: function(file) {
            var start1 = 0;
            var end1 = (file.size < 4096) ? file.size : 4096;
            var promise1 = this.uploader.md5File(file, start1, end1);

            var start2 = parseInt(file.size / 3);
            var end2 = ((start2 + 4096) > file.size) ? file.size : start2 + 4096;
            var promise2 = this.uploader.md5File(file, start2, end2);

            var start3 = parseInt(file.size / 3 * 2);
            var end3 = ((start3 + 4096) > file.size) ? file.size : start3 + 4096;
            var promise3 = this.uploader.md5File(file, start3, end3);

            var start4 = ((file.size - 4096) < 0) ? 0 : file.size - 4096;
            var end4 = file.size;
            var promise4 = this.uploader.md5File(file, start4, end4);

            var deferred = WebUploader.Deferred();
            WebUploader.when(promise1, promise2, promise3, promise4).done(function(hash1, hash2, hash3, hash4) {
                var hash = hash1.slice(0, 8);
                hash += hash2.slice(8, 16);
                hash += hash3.slice(16, 24);
                hash += hash4.slice(24, 32);
                deferred.resolve('cmd5|' + hash);

            });
            return deferred.promise();
        },

        getFileProcessor: function(file) {
            var extProcessors = {
                'mp4': 'video',
                'avi': 'video',
                'flv': 'video',
                'wmv': 'video',
                'mov': 'video',
                'rmvb': 'video',
                'vob': 'video',
                'mpg': 'video',
                'doc': 'document',
                'docx': 'document',
                'pdf': 'document',
                'ppt': 'document',
                'pptx': 'document'
            };

            var dotPos = file.name.lastIndexOf('.');
            if (dotPos < 0) {
                return '';
            }
            var ext = file.name.slice(dotPos);
            if (!extProcessors[ext]) {
                return '';
            }

            return extProcessors[ext];
        }

    });

    module.exports = BatchUploader;

});