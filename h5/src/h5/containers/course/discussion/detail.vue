<template>
  <div class="discussion-detail">
    <div class="detail-header">
      <van-icon
        name="cross"
        size="18px"
        color="#000"
        @click="handleClickGoToList"
      />
      <h3 class="detail-header__title">话题详情</h3>
      <span class="detail-header__btn">回复</span>
    </div>

    <div class="discussion-body">
      <div class="discussion-body__info">
        <img class="avatar" src="http://try6.edusoho.cn/files/user/2021/04-22/1528579ec515309441.png">
        <div class="info-right">
          <span class="info-nickname">曲敬良发起</span>
          <span class="info-time">20:25</span>
        </div>
      </div>
      <div class="discussion-body__title">Photoshop基础入门班级课程阿建设大街</div>
      <div class="discussion-body__content">
        Photoshop基础入门班级Photoshop基础入门班级Photoshop础入门班级Photoshop基础入门班级Photoshop基础入门班级大开杀戒打卡机卡萨丁就爱看山东矿机奥斯卡了低级趣味坡地区欧派我看到强迫我看懂情况的迫切看我的皮卡丘颇为读卡器坡屋顶看破情况的迫切我看到我都快亲我阿斯达是阿
      </div>
    </div>

    <van-list
      v-model="loading"
      :finished="finished"
      finished-text="没有更多了"
      @load="fetchCourseThreadPost"
    >
      <reply-item v-for="item in replyList" :key="item.id" :item="item" />
    </van-list>

    <div class="reply">
      <van-field
        ref="replyInput"
        v-model="content"
        placeholder="回复..."
        @keyup.enter="handleClickEnter"
      />
    </div>
  </div>
</template>

<script>
import Api from '@/api';
import _ from 'lodash';
import ReplyItem from './components/ReplyItem.vue'

export default {
  name: 'DiscussionDetail',

  components: {
    ReplyItem
  },

  props: {
    id: {
      type: String,
      required: true
    }
  },

  data() {
    return {
      content: '',
      courseId: this.$route.params.id,
      loading: false,
      finished: false,
      replyList: []
    }
  },

  created() {
    this.fetchCourseThreadPost();
  },

  methods: {
    async fetchCourseThreadPost() {
      const result = await Api.getCoursesThreadPost({
        query: {
          courseId: this.courseId,
          threadId: this.id
        },
        params: {
          limit: 10,
          offset: 0
        }
      });
      console.log(result);
    },

    handleClickGoToList() {
      this.$emit('change-current-component', { component: 'List' });
    },

    async handleClickEnter() {
      const content = _.trim(this.content);

      if (!content) return;

      const result = await Api.createCoursesThreadPost({
        query: {
          courseId: this.courseId,
          threadId: this.id
        },
        data: {
          content
        }
      });

      this.replyList.unshift(result);
      this.content = '';
    }
  }
}
</script>

<style lang="scss" scoped>
.discussion-detail {
  padding-bottom: vw(60);

  .detail-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: vw(16);

    &__title {
      margin: 0;
      font-size: vw(16);
      font-weight: 500;
      color: #333;
      line-height: vw(24);
    }

    &__btn {
      visibility: hidden;
    }
  }


  .discussion-body {
    padding: vw(2) vw(16) vw(16);
    border-bottom: vw(8) solid #f5f5f5;

    &__info {
      display: flex;

      .avatar {
        margin-right: vw(8);
        width: vw(42);
        height: vw(42);
        border-radius: 50%;
      }

      .info-right {
        display: flex;
        flex-direction: column;
        justify-content: space-around;

        .info-nickname {
          font-size: vw(14);
          color: #666;
          line-height: vw(20);
        }

        .info-time {
          font-size: vw(12);
          color: #999;
          line-height: vw(16);
        }
      }
    }

    &__title {
      margin-top: vw(8);
      font-size: vw(16);
      font-weight: 500;
      color: #333;
      line-height: vw(24);
    }

    &__content {
      margin-top: vw(8);
      font-size: vw(12);
      color: #666;
      line-height: vw(16);
    }
  }

  .reply {
    position: fixed;
    bottom: vw(8);
    left: 50%;
    transform: translateX(-50%);
    width: vw(340);

    .van-cell {
      background: #f5f5f5;
      border-radius: 24px;
    }
  }

  .van-list {
    margin-top: 0;
  }
}
</style>
