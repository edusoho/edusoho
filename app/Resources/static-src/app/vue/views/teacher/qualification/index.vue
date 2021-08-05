<template>
  <div class="teacher-qualification">
    <div class="banner text-center">教师资质公示</div>

    <div class="container">
      <a-row>
        <a-col v-for="item in qualificationList" :key="item.id" :xs="12" :sm="8" :lg="6" :xl="4">
          <div class="qualification-item mt24 text-center">
            <div class="img-box">
              <img v-lazy="item.imgUrl">
            </div>
            <div class="name">{{ item.name }}</div>
            <p>教师资格证编号</p>
            <p>{{ item.id }}</p>
          </div>
        </a-col>
      </a-row>
    </div>
    <back-to-top />
  </div>
</template>

<script>
import BackToTop from 'app/vue/components/BackToTop.vue';

const qualificationList = [];

for (let index = 0; index < 100; index++) {
  qualificationList.push({
    imgUrl: 'data:image/jpeg;base64,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',
    name: '方丽琰',
    id: '20191100123003393'
  });
}

export default {
  name: 'Teacherqualification',

  components: {
    BackToTop
  },

  data() {
    return {
      qualificationList
    }
  }
}
</script>

<style lang="less" scoped>
.banner {
  padding-top: 40px;
  height: 120px;
  font-size: 32px;
  color: #fff;
  line-height: 40px;
  font-weight: 500;
  background: url('/static-dist/app/img/vue/teacher/qualification_bg.png') center no-repeat;
}

.qualification-item {
  padding: 0 12px;

  .img-box {
    position: relative;
    padding-bottom: 100%;
    width: 100%;

    img {
      position: absolute;
      left: 0;
      top: 0;
      width: 100%;
      height: 100%;
      border-radius: 8px;
    }
  }

  .name {
    margin-top: 8px;
    color: #333;
  }

  p {
    margin-top: 4px;
    margin-bottom: 0;
    font-size: 12px;
    color: #666;
    line-height: 16px;
  }
}
</style>
