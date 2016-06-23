define(function(require, exports, module) {

    localUploader();
    changeTab();
    netUploader();

    function netUploader() {
        $("#net-image-btn").on('click',function(){
            var imageurl = $(this).prev().val();
            var $imageurl = "<p><span class='colse icon-close es-icon es-icon-delete'></span><img src='" + imageurl + "'/></p>"
            if (imageurl.length > 0) {
                $(".net-image-list").append($imageurl);
            } else {
                alert("请输入图片地址！");
            }
        });
        $("#network-image").on("click", ".colse", function() {
            $(this).parent().remove();
        })
    }

    function localUploader() {
        require('webuploader');
        var uploaderpath = require.resolve("webuploader").match(/[^?#]*\//)[0];
        var $hzpicker = $("#hzpicker");
        var formData = $.extend({}, {
            token: $hzpicker.data("uploadToken")
        });
        var $storehouseimage = $("#storehouse-image");
        var uploader = WebUploader.create({
            // swf文件路径
            swf: uploaderpath + 'Uploader.swf',
            auto: true,
            // 文件接收服务端。
            server: 'test',

            // 选择文件的按钮。可选。
            // 内部根据当前运行是创建，可能是input元素，也可能是flash.
            pick: '#hzpicker',

            // 不压缩image, 默认如果是jpeg，文件上传前会压缩一把再上传！
            resize: false,
            // 只允许选择图片文件。
            formData: $.extend(formData, {
                '_csrf_token': $('meta[name=csrf-token]').attr('content')
            }),
            accept: {
                title: 'Images',
                extensions: 'gif,jpg,jpeg,bmp,png',
                mimeTypes: 'image/*'
            }
        });
        uploader.addButton({
            id: '#picker-li'
        });
        // 当有文件被添加进队列的时候
        uploader.on('fileQueued', function(file) {
            $("#un-upload").hide();
            $hzpicker.attr("data-uploadFinished", "0");
            var $li = $('<li><div id="' + file.id + '" class="file-item "><img /><span class="colse icon-close es-icon es-icon-delete"></span><span class="es-icon icon-complete es-icon-done"></span></div></li>'),
                $img = $li.find('img');
            $li.on('click', '.colse', function() {
                $(this).closest(".file-item").remove();
                $("." + file.id).remove();
                uploader.removeFile(file, true);
            });
            $storehouseimage.append("<p class='" + file.id + "'></p>");
            $li.insertBefore($(".js-picker-box"));
            $("#thelist").show();
            uploader.makeThumb(file, function(error, src) {
                if (error) {
                    $img.replaceWith('<span>不能预览</span>');
                    return;
                }
                $img.attr('src', src);
            }, 100, 100);
        });
        uploader.on('uploadProgress', function(file, percentage) {
            var $li = $('#' + file.id),
                $percent = $li.find('.progress .progress-bar');
            if (!$percent.length) {
                $percent = $('<div class="progress progress-striped">' +
                    '<div class="progress-bar" role="progressbar" style="width: 0%">' +
                    '</div>' +
                    '</div>').appendTo($li).find('.progress-bar');
            }
            $percent.css('width', percentage * 100 + '%');
        });
        uploader.on('uploadSuccess', function(file, response) {
            uploader.removeFile(file, true);
            $('#' + file.id).addClass('upload-state-done');
            $("." + file.id).html("<img src='" + response.url + "' title='" + file.name + "'/>")
        });
        uploader.on("uploadFinished", function() {
            $hzpicker.attr("data-uploadFinished", "1");
        });
    }

    function changeTab() {
        $('#myTab a').on('click', function() {
            var $this = $(this);
            $('#myTab a').removeClass('active');
            $this.addClass('active');
            $('#myuploadContent').find('.tab-pane').removeClass('active');
            $($this.attr('data-href')).addClass('active');
        });
    }

});