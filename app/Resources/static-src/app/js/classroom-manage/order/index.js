$('.classroom-order-js-export-btn').on('click', async function () {
  const $form = $('#sign-statistics');

  // 获取表单内的所有参数并转为对象
  const rawParams = Object.fromEntries(new FormData($form[0]));

  // 添加额外的参数
  rawParams.start = rawParams.start || 0;

  // 过滤掉 undefined 的参数
  const params = Object.fromEntries(
    Object.entries(rawParams).filter(([_, v]) => v !== undefined)
  );
  try {
    const query = new URLSearchParams(params).toString();
    const verificationResponse = await fetch(`/secondary/verification?exportFileName=classroomOrder&targetFormId=${params['classroomId']}&${query}`);
    const html = await verificationResponse.text();
    // 显示 modal
    $('#modal').html(html).modal('show');
  } catch (error) {
    console.error('请求出错:', error);
  }
});