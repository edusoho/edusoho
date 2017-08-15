import 'myclabs.jquery.confirm';

$.confirm.options = {
  title: '',
  confirmButton: Translator.trans('site.confirm'),
  cancelButton: Translator.trans('site.cancel'),
  post: false,
  submitForm: false,
  confirmButtonClass: "btn-primary btn-sm",
  cancelButtonClass: "btn-default btn-sm",
  dialogClass: "modal-dialog modal-sm"
}