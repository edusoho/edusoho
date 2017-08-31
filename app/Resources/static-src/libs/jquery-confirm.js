import 'myclabs.jquery.confirm';

$.confirm.options = {
  title: '',
  confirmButton: Translator.trans('site.confirm'),
  cancelButton: Translator.trans('site.cancel'),
  post: false,
  submitForm: false,
  confirmButtonClass: "cd-btn cd-btn-flat-danger cd-btn-lg",
  cancelButtonClass: "cd-btn cd-btn-flat-default cd-btn-lg",
  dialogClass: "modal-dialog cd-modal-dialog"
}