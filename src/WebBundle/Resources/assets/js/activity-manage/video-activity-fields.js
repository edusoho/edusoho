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


// $("#material a").click(function (e) {
//     e.preventDefault()
//     $(this).tab('show')
// });


// class MaterialLibChoose {

//     constructor($modal, mediaType) {
//         this.modal = $modal;
//         this.mediaType = mediaType;
//         this.loadShareingContacts = false;
//         this._init();
//         this._initEvent();
//     }

//     _init() {
//         this._loadList();
//     }

//     _initEvent() {
//         $(this.modal).on('click', '.js-material-type', this.onClickMaterialType.bind(this));
//         $(this.modal).on('click', '.js-browser-search', this._submitForm.bind(this));
//     }

//     _loadList() {
//         let url = $('.js-browser-search').data('url');

//         let params = {};
//         params.sourceFrom = $('input[name=sourceFrom]').val();
//         params.page = $('input[name=page]').val();
//         $('.js-material-list').load(url, params, function () {
//             console.log('page is on loading')
//         })
//     }


//     onClickMaterialType(event) {
//         let that = event.currentTarget;
//         var type = $(that).data('type');
//         $(that).removeClass('active').addClass('active');
//         $('input[name=sourceFrom]').val(type);
//         switch (type) {
//             case 'my' :
//                 $('.js-file-name-group').removeClass('hidden');
//                 $('.js-file-owner-group').addClass('hidden');
//                 break;
//             case 'sharing':
//                this._loadSharingContacts($(that).data('sharingContactsUrl'));
//                 $('.js-file-name-group').removeClass('hidden');
//                 $('.js-file-owner-group').removeClass('hidden');

//                 break;
//             default:
//                 $('.js-file-name-group').addClass('hidden');
//                 $('.js-file-owner-group').addClass('hidden');
//                 break;
//         }
//     }

//     _loadSharingContacts(url) {
//         $('.js-file-owner').load(url, function(teachers){
//             console.log(teachers,isNaN(Object), Object.keys(teachers).length);
//             if (Object.keys(teachers).length > 0) {
//                 var html=`<option value=''>${Translator.trans('请选择老师')}</option>`;
//                 $.each(teachers, function (i, teacher) {
//                     html += `<option value='${teacher.id}'>${teacher.nickname} </option>`
//                 });

//                 $(".js-file-owner", self.element).html(html);
//             }
//         })
//         console.log('hihihi',url)
//     }


//     _submitForm() {
//         let keyword = $('.js-file-name').val();

//         if (keyword.trim() == '') {
//             alert('条件不能为空')
//             return false;
//         }
//         let params = {};
//         params.keyword = keyword
//         params.sourceFrom = $('input[name=sourceFrom]').val();
//         params.page = $('input[name=page]').val();

//         let url = $('.js-browser-search').data('url');
//         $('.js-material-list').load(url, params, function () {
//             console.log('page is reloading')
//         })
//     }
// }

// new MaterialLibChoose($('#video-chooser-disk-pane'), 'video');
