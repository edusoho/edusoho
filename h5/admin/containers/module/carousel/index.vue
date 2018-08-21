<template>
  <div class="setting-carousel">
    <div class="carousel-image">
      <img src="http://zyc.st.edusoho.cn/themes/jianmo/img/banner_app.jpg" alt="">
    </div>
    <div class="carousel-allocate">
      <header class="title">轮播图设置</header>
      <div class="carousel-item clearfix">
        <div class="add-img"><i class="text-18">+</i> 添加图片</div>
        <div class="add-title f-left">标题：<input type="text" placeholder="请输入标题"></div>
        <div class="f-left">链接：<button class="btn-gray btn-choose-course">选择课程</button></div>
      </div>
      <div class="btn-gray btn-add-item">添加一个轮播图</div>
    </div>
  </div>
</template>

<script>

export default {

  data() {
    return  {
      parts: [{
        data:[
          {
            image: 'http://zyc.st.edusoho.cn/themes/jianmo/img/banner_app.jpg',
            link: {
              type: 'url',
              // url: 'http://zyc.st.edusoho.cn'
            }
          },
          {
            image: 'http://zyc.st.edusoho.cn/themes/jianmo/img/banner_app.jpg',
            link: {
              type: 'url',
              // url: 'http://zyc.st.edusoho.cn'
            }
          }
        ],
      }]
    }
  }
}
</script>
