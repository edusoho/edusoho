import Register from './register';
import drag from './drag';
new Register();

let $dragBtn = document.getElementById('drag-btn');


drag($dragBtn, $dragBtn, (right, top) => {
  if (right == '0px') {
    cd.message({ type: 'success', message: '验证成功' });
  } else {
    cd.message({ type: 'danger', message: '验证失败' });
  }
});
