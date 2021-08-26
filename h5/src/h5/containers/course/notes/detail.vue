<template>
  <div class="note-detail">
    <div class="detail-header">
      <van-icon
        name="cross"
        size="18px"
        color="#000"
        @click="handleClickGoToList"
      />
      <h3 class="detail-header__title">{{ $t('courseLearning.noteDetails') }}</h3>
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
            <span class="like-num" :class="{ like: isLike }" @click="handleClickLike">
              <i class="iconfont icon-like"></i>
              {{ noteDetail.likeNum }}
            </span>
          </div>
        </div>
      </div>
      <div class="note-body__content" v-html="note.content" />
    </div>
  </div>
</template>

<script>
import _ from 'lodash';
import Api from '@/api';

export default {
  name: 'NoteDetail',

  props: {
    note: {
      type: Object,
      required: true
    }
  },

  data() {
    return {
      noteDetail: this.note,
      courseId: this.$route.params.id
    }
  },

  computed: {
    isLike() {
      return !!_.size(this.noteDetail.like);
    }
  },

  methods: {
    handleClickGoToList() {
      this.$emit('change-current-component', { component: 'List' });
    },

    handleClickLike() {
      const { id, likeNum } = this.noteDetail;

      const query = {
        courseId: this.courseId,
        noteId: id
      };

      const note = this.isLike ? { like: {}, likeNum: likeNum - 1 } : { like: { status: 1 }, likeNum: (likeNum * 1) + 1 };

      this.isLike ? Api.cancelNoteLike({ query }) : Api.noteLike({ query });

      _.assign(this.noteDetail, note);
    }
  }
}
</script>

<style lang="scss" scoped>
.note-detail {
  padding-bottom: vw(60);

  .detail-header {
    position: relative;
    padding: vw(16);
    text-align: center;

    .van-icon {
      position: absolute;
      left: vw(16);
      top: 50%;
      transform: translateY(-50%);
    }

    &__title {
      margin: 0;
      font-size: vw(16);
      font-weight: 500;
      color: #333;
      line-height: vw(24);
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
        }

        .info-task {
          display: inline-block;
          max-width: vw(150);
          color: $primary-color;
        }

        .like-num {
          color: #999;
        }

        .like {
          color: $primary-color;
        }
      }
    }

    &__content {
      margin-top: vw(16);
      color: #333;
      word-break: break-word;

      /deep/ img {
        max-width: 100%;
      }
    }
  }
}
</style>
