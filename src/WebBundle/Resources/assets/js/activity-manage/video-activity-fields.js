//设置任务高度；
var $parentiframe = $(window.parent.document).find('#task-manage-content-iframe');

setTimeout(() => {
    
    let $uploader = $('#uploader-container');

    let uploaderSdk = new UploaderSDK({
        id: $uploader.attr('id'),
        initUrl: $uploader.data('initUrl'),
        accept: $uploader.data('accept'),
        process: $uploader.data('process')
    });

    console.log($("iframe").contents().find('body').height());
    $("#iframe").height($("iframe").contents().find('body').height());
    $parentiframe.height($parentiframe.contents().find('body').height());

    $("#material a").click(function (e) {
        e.preventDefault()
        $(this).tab('show')
    });
}, 1000);


