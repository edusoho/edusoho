<template>
  <div class="note-detail">
    <div class="detail-header">
      <van-icon
        name="cross"
        size="18px"
        color="#000"
        @click="handleClickGoToList"
      />
      <h3 class="detail-header__title">笔记详情</h3>
      <span class="detail-header__btn">回复</span>
    </div>

    <div class="note-body">
      <div class="note-body__info">
        <img class="avatar" :src="note.user.avatar.small">
        <div class="info-right">
          <span>{{ note.user.nickname }}</span>
          <div class="info-right-bottom">
            <div class="info">
              <span class="info-time">{{ note.createdTime | formatCourseTime }}</span>
              <span class="info-task text-overflow">{{ note.task.title }}</span>
            </div>
            <span class="like-num">{{ note.likeNum }}</span>
          </div>
        </div>
      </div>
      <div class="note-body__content" v-html="note.content" />
    </div>
  </div>
</template>

<script>
export default {
  name: 'NoteDetail',

  props: {
    note: {
      type: Object,
      required: true
    }
  },

  methods: {
    handleClickGoToList() {
      this.$emit('change-current-component', { component: 'List' });
    }
  }
}
</script>

<style lang="scss" scoped>
.note-detail {
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


  .note-body {
    padding: vw(2) vw(16) vw(16);
    font-size: vw(14);
    color: #666;
    line-height: vw(20);

    &__info {
      display: flex;

      .avatar {
        margin-right: vw(8);
        width: vw(42);
        height: vw(42);
        border-radius: 50%;
      }

      .info {
        display: flex;
        align-items: center;
      }

      .info-right {
        display: flex;
        flex-direction: column;
        justify-content: space-around;
        flex-grow: 1;

        .info-right-bottom {
          display: flex;
          justify-content: space-between;
          align-items: center;
        }

        .info-time {
          margin-right: vw(8);
          font-size: vw(12);
          color: #999;
          line-height: vw(16);
        }

        .info-task {
          display: inline-block;
          max-width: vw(150);
          color: $primary-color;
        }

        .like-num {
          color: #999;
        }
      }
    }

    &__content {
      margin-top: vw(16);
      color: #333;

      /deep/ img {
        max-width: 100%;
      }
    }
  }
}
</style>
