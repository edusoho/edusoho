<template>
  <div class="teacher-qualification">
    <div class="banner text-center">教师资质公示</div>

    <div class="container">
      <a-row>
        <a-col v-for="item in qualificationList" :key="item.id" :xs="12" :sm="8" :lg="6" :xl="4">
          <div class="qualification-item mt24 text-center">
            <div class="img-box">
              <img v-lazy="item.url">
            </div>
            <div class="name">{{ item.profile.truename }}</div>
            <p>教师资格证编号</p>
            <p>{{ item.code }}</p>
          </div>
        </a-col>
      </a-row>
    </div>
    <back-to-top />
  </div>
</template>

<script>
import BackToTop from 'app/vue/components/BackToTop.vue';
import { TeacherQualification } from 'common/vue/service';

export default {
  name: 'TeacherQualification',

  components: {
    BackToTop
  },

  data() {
    return {
      qualificationList: []
    }
  },

  created() {
    this.fetchTeacherQualification();
  },

  methods: {
    async fetchTeacherQualification() {
      const { data } = await TeacherQualification.search({ limit: 10000 });
      this.qualificationList = data;
    }
  }
}
</script>

<style lang="less" scoped>
.teacher-qualification {
  min-height: 540px;

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
}
</style>
