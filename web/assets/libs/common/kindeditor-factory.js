define(function(require, exports, module) {

    require('kindeditor');

    var simpleItems = ['bold', 'italic', 'underline', 'forecolor', '|', 'insertorderedlist', 'insertunorderedlist', '|', 'link', 'unlink', 'image', '|', 'removeformat', 'source'];

    var standardItems = [
        'bold', 'italic', 'underline', 'strikethrough', 'removeformat', '|',
        'fontsize', 'forecolor', 'hilitecolor',   '|', 
        'link', 'unlink', '|',
        'image', 'flash',  'code',  '|',
        'insertorderedlist', 'insertunorderedlist','indent', 'outdent', '|',
        'justifyleft', 'justifycenter', 'justifyright', '|',
        'source',  'fullscreen', 'about'
    ];

    var fullItems = [
        'bold', 'italic', 'underline', 'strikethrough', '|',
        'link', 'unlink', '|',
        'insertorderedlist', 'insertunorderedlist','indent', 'outdent', '|',
         'image', 'flash', 'insertfile', 'code', 'table', 'hr', '/',
        'formatblock', 'fontname', 'fontsize', '|',
        'forecolor', 'hilitecolor',   '|', 
        'justifyleft', 'justifycenter', 'justifyright', 'justifyfull',  '|',
        'removeformat', 'clearhtml', '|',
        'source', 'preview',  'fullscreen', '|',
        'about'
    ];

    var contentCss = [];
    contentCss.push('body {font-size: 14px; line-height: 1.428571429;color: #333333;}');
    contentCss.push('a {color: #428bca;}');
    contentCss.push('p {margin: 0 0 10px;}');

    var defaultConfig = {
        width: '100%',
        resizeType: 1,
        uploadJson: app.config.editor_upload_path,
        extraFileUploadParams: {},
        filePostName: 'file',
        cssData: contentCss.join('\n')
    };

    var configs = {};
    configs.simple = $.extend({}, defaultConfig, {items:simpleItems});
    configs.standard = $.extend({}, defaultConfig, {items:standardItems});
    configs.full = $.extend({}, defaultConfig, {items:fullItems});

    function getConfig(name, extendConfig) {
        if (!extendConfig) {
            extendConfig = {};
        }
        return $.extend({}, configs[name], extendConfig);
    }

    exports.create = function(select, name, config) {
        return KindEditor.create(select, getConfig(name, config));
    }
});