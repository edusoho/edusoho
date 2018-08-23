<template>
  <div class="setting-carousel">
    <div class="carousel-image">
      <img src="http://zyc.st.edusoho.cn/themes/jianmo/img/banner_app.jpg" alt="">
    </div>
    <div class="carousel-allocate">
      <header class="title">轮播图设置</header>
      <div class="carousel-item clearfix" v-for="(part, index) in parts[0].data" @click="selected(part)">
        <van-uploader :after-read="onRead" class="add-img">
          <img class='carousel-img' :src="part.image"/>
          <span v-show="!part.image"><i class="text-18">+</i> 添加图片</span>
        </van-uploader>
        <div class="add-title pull-left">标题：<input type="text" placeholder="请输入标题"></div>
        <div class="pull-left">链接：<button class="btn-gray btn-choose-course">选择课程</button></div>
      </div>
      <div class="btn-gray btn-add-item" @click="addItem">添加一个轮播图</div>
    </div>
  </div>
</template>

<script>

export default {

  data() {
    return  {
      imgAdress: '',
      parts: [{
        data:[
          {
            image: 'http://www.esdev.com/themes/jianmo/img/banner_net.jpg',
            link: {
              type: 'url',
              // url: 'http://zyc.st.edusoho.cn'
            }
          }
        ],
      }]
    }
  },
  created() {
    Api.saveDraftDate({
      query: {
        portal: 'h5',
        type : 'discovery'
      }
    })
    .then((data) => {
      console.log(data)
      this.parts = data;
    })
    .catch((err) => {
      console.log(err, 'error');
    });
  },
  methods: {
    onRead(file) {
      this.parts[0].data[0].image = file.content;
    },
    addItem() {
      console.log('add one');
    },
    selected(part) {
      console.log(part)
    }
  }
}
</script>
