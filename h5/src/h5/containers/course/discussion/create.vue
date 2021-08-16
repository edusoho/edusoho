<template>
  <div class="discussion-create">
    <div class="create-header">
      <van-icon
        name="cross"
        size="18px"
        color="#000"
        @click="handleClickGoToList"
      />
      <h3 class="create-header__title">发话题</h3>
      <span class="create-header__btn" @click="createDiscussion">发布</span>
    </div>

    <van-form ref="form">
      <div class="discussion-create__title">
        <van-field
          v-model="title"
          placeholder="发起话题标题"
          :rules="[{ required: true, message: '请输入标题' }]"
        />
      </div>

      <div class="discussion-create__content">
        <van-field
          v-model="content"
          type="textarea"
          autosize
          placeholder="发起话题内容..."
          :rules="[{ required: true, message: '请输入内容' }]"
        />
      </div>
    </van-form>
  </div>
</template>

<script>
import Api from '@/api';

export default {
  name: 'DiscussionCreate',

  props: {
    type: {
      type: String,
      required: true
    }
  },

  data() {
    return {
      title: '',
      content: ''
    }
  },

  methods: {
    handleClickGoToList() {
      this.$emit('change-current-component', { component: 'List' });
    },

    createDiscussion() {
      this.$refs.form.validate().then(async () => {
        const courseId = this.$route.params.id;

        await Api.createCoursesThread({
          query: {
            courseId
          },
          data: {
            content: this.content,
            courseId,
            type: this.type,
            title: this.title
          }
        });
        this.handleClickGoToList();
      });
    }
  }
}
</script>


<style lang="scss" scoped>
.discussion-create {

  .create-header {
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
      font-size: 16px;
      color: $primary-color;
      line-height: 24px;
    }
  }

  &__title {
    line-height: vw(24);
    border-bottom: 1px solid #f5f5f5;

    .van-cell {
      font-size: vw(16);
      font-weight: 500;
      color: #999;
    }
  }

  &__content {
    padding-top: vw(6);

    .van-cell {
      color: #999;
    }
  }
}
</style>
