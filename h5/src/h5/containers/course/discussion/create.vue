<template>
  <div class="discussion-create">
    <div class="create-header">
      <van-icon
        name="cross"
        size="18px"
        color="#000"
        @click="handleClickGoToList"
      />
      <h3 class="create-header__title">{{ text.title }}</h3>
      <span class="create-header__btn" @click="createDiscussion">{{ $t('courseLearning.publish') }}</span>
    </div>

    <van-form ref="form">
      <div class="discussion-create__title">
        <van-field
          v-model="title"
          :error="false"
          :placeholder="text.placeholderTitle"
          :rules="[{ required: true, message: $t('courseLearning.pleaseEnterATitle') }]"
        />
      </div>

      <div class="discussion-create__content">
        <van-field
          v-model="content"
          type="textarea"
          rows="1"
          :error="false"
          autosize
          :placeholder="text.placeholderContent"
          :rules="[{ required: true, message: $t('courseLearning.pleaseEnterContent') }]"
        />
      </div>
    </van-form>
  </div>
</template>

<script>
import _ from 'lodash';
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

  computed: {
    text() {
      const langKey = {
        discussion: {
          title: 'courseLearning.sendQA',
          placeholderTitle: 'courseLearning.QATitle',
          placeholderContent: 'courseLearning.QAContent'
        },
        question: {
          title: 'courseLearning.postTopic',
          placeholderTitle: 'courseLearning.topicTitle',
          placeholderContent: 'courseLearning.topicContent'
        }
      }
      const langObj = langKey[this.type];

      _.forEach(langObj, (value, key) => {
        langObj[key] = this.$t(value);
      });

      return langObj;
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

    &__btn {
      position: absolute;
      right: vw(16);
      top: 50%;
      transform: translateY(-50%);
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
