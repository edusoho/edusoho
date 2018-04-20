import Register from './register';
new Register();

let test = document.getElementById('test');
test.onmousedown = (event) => {
  console.log('触发按下');
  let flag;
  const $target = $(event.target);
  let distanceX = event.clientX - $target.offset().left;
  let distanceY = $target.offset().top - event.clientY;
  const left = $target.parent().position().left;
  let selfLeft = $target.offset().left;
  console.log($target.offset().top);
  console.log(event.clientY);
  // console.log(distanceY);
  // console.log(distanceX);


  if (distanceX > 0 && distanceX < 66) {
    flag = true;
  }
  test.onmousemove = (event) => {
    console.log(flag);
    if (flag) {
      $target.css('cursor', 'move');
      const space = event.clientX;
      $target.css('left', space - left - distanceX + 'px');
      $target.css('top', 0);
      let selfLeft = $target.offset().left;
      if (selfLeft + 66 >= 889) {
        $target.css('left', '271px');
      }
      if (selfLeft <= 551) {
        $target.css('left', '0px');
      }
    }
  };

  document.onmouseup = () => {
    console.log('放开');
    test.onmousemove = null;
    document.onmouseup = null;
    $target.css('cursor', 'default');
    const currentTarget = $target.offset().left;
    if (currentTarget == 822) {
      cd.message({ type: 'success', message: '验证成功' });
    } else {
      cd.message({ type: 'danger', message: '验证失败' });
    }
    flag = false;
  };
};


