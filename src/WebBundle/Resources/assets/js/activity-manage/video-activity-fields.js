
const $parentIframe = $(window.parent.document).find('#task-manage-content-iframe');

let $uploader = $('#uploader-container');
const $iframe = $("iframe");
let uploaderSdk = new UploaderSDK({
    id: $uploader.attr('id'),
    initUrl: $uploader.data('initUrl'),
    finishUrl: $uploader.data('finishUrl'),
    accept: $uploader.data('accept'),
    process: $uploader.data('process')
});
uploaderSdk.process = {
    "videoQuality": "high",
    "audioQuality": "high"
};
console.log($iframe.contents().find('body').height());
$("#iframe").height($iframe.contents().find('body').height());
$parentIframe.height($parentIframe.contents().find('body').height());

$("#material a").click(function (e) {
    e.preventDefault();
    $(this).tab('show')
});