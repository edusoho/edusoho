import ConfirmModal from './confirm';

export default class PaySDK {

  pay($params) {
    let cf = new ConfirmModal();
    cf.show();
  }
}
