/**
 * Created by Simon on 31/10/2016.
 */
let $uploader = $('#uploader-container');

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