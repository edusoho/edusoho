
console.log(window);
console.log(window.UploaderSDK);

let uploaderSdk = new window.UploaderSDK({

});

$("#material a").click(function (e) {
    e.preventDefault()
    $(this).tab('show')
});