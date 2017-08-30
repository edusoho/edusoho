import 'myclabs.jquery.confirm';

$.confirm.options = {
  title: '',
  confirmButton: Translator.trans('site.confirm'),
  cancelButton: Translator.trans('site.cancel'),
  post: false,
  submitForm: false,
  confirmButtonClass: "cd-btn cd-btn-primary",
  cancelButtonClass: "cd-btn cd-btn-flat-default",
  dialogClass: "modal-dialog modal-sm"
}