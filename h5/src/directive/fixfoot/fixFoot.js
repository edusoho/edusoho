let listenAction;
let originalHeight;
let currHeight;
export default {
  inserted(el) {
    const elStyle = el.style;
    let active = false;
    originalHeight = document.body.clientHeight;
    const reset = () => {
      if (!active) {
        return;
      }
      elStyle.position = 'fixed';
      active = false;
    };
    const hang = () => {
      if (active) {
        return;
      }
      elStyle.position = 'static';
      active = true;
    };
    const getCurrHeight = () => {
      const getHeight = document.body.clientHeight;
      return getHeight;
    };
    const check = () => {
      currHeight = getCurrHeight();
      if (currHeight != originalHeight) {
        hang();
      } else {
        reset();
      }
    };
    listenAction = () => {
      check();
    };
    window.addEventListener('resize', listenAction);
  },
  unbind() {
    window.removeEventListener('resize', listenAction);
  }
};
