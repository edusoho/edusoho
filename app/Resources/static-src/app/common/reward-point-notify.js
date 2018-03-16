import notify from 'common/notify';

export default class RewardPointNotify {
  constructor() {
    this.storage = window.localStorage;
    this.STORAGE_NAME = 'reward-point-notify-queue';
    this.init();
  }

  init() {
    let storageStr = this.storage.getItem(this.STORAGE_NAME);
    if (!storageStr) {
      this.stack = [];
    } else {
      this.stack = JSON.parse(storageStr);
    }
  }

  display() {

    if (this.stack.length > 0) {
      let msg = this.stack.pop();
      notify('success', decodeURIComponent(msg));
      this.store();
    }
  }

  store() {
    this.storage.setItem(this.STORAGE_NAME, JSON.stringify(this.stack));
  }

  push(msg) {
    if (msg) {
      this.stack.push(msg);
      this.store();
    }
  }

  size() {
    return this.stack.size();
  }

}