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
      <span class="create-header__btn" @click="createDiscussion">{{
        $t('courseLearning.publish')
      }}</span>
    </div>

    <van-form ref="form">
      <div class="discussion-create__title">
        <van-field
          v-model="title"
          :error="false"
          :placeholder="text.placeholderTitle"
          :rules="[
            { required: true, message: $t('courseLearning.pleaseEnterATitle') },
          ]"
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
          :rules="[
            {
              required: true,
              message: $t('courseLearning.pleaseEnterContent'),
            },
          ]"
        />
      </div>
      <div class="discussion-create__upload">
        <van-uploader
          v-model="fileList"
          :after-read="afterRead"
          :max-count="6"
          @delete="deleteImgItem"
        />
      </div>
    </van-form>
  </div>
</template>

<script>
import _ from 'lodash';
import Api from '@/api';
import { Toast } from 'vant';

export default {
  name: 'DiscussionCreate',

  props: {
    type: {
      type: String,
      required: true,
    },
  },

  data() {
    return {
      title: '',
      content: '',
      fileList: [],
      imgs: [],
    };
  },

  computed: {
    text() {
      const langKey = {
        question: {
          title: 'courseLearning.sendQA',
          placeholderTitle: 'courseLearning.QATitle',
          placeholderContent: 'courseLearning.QAContent',
        },
        discussion: {
          title: 'courseLearning.postTopic',
          placeholderTitle: 'courseLearning.topicTitle',
          placeholderContent: 'courseLearning.topicContent',
        },
      };
      const langObj = langKey[this.type];

      _.forEach(langObj, (value, key) => {
        langObj[key] = this.$t(value);
      });

      return langObj;
    },
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
            courseId,
          },
          data: {
            content: this.content,
            courseId,
            type: this.type,
            title: this.title,
            imgs: this.imgs,
          },
        });
        this.handleClickGoToList();
      });
    },
    afterRead(file) {
      const formData = new FormData();
      formData.append('file', file.content);
      formData.append('group', 'course');
      Api.updateFile({
        data: formData,
      })
        .then(res => {
          this.imgs.push(res.uri);
        })
        .catch(err => {
          Toast.fail(err.message);
        });
    },
    deleteImgItem(e, detail) {
      this.imgs.splice(detail.index, 1);
    },
  },
};
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

  &__upload {
    margin: vw(16);

    /deep/.van-uploader__wrapper :nth-child(4) {
      margin: 0 0;
    }
    /deep/.van-uploader__upload {
      margin: 0 0;
      width: vw(72) !important;
      height: vw(72) !important;
      border-radius: vw(4);
      overflow: hidden;
    }
    /deep/.van-uploader__preview {
      margin: 0 vw(16) vw(16) 0;
    }
    /deep/.van-uploader__preview-image {
      width: vw(72) !important;
      height: vw(72) !important;
      border-radius: vw(4);
      overflow: hidden;
    }
    /deep/.van-uploader__preview-delete {
      border-radius: 50%;
    }

    /deep/.van-uploader__preview-delete-icon {
      position: absolute;
      top: vw(-1);
      right: vw(-1);
      color: #fff;
      font-size: vw(16);
      -webkit-transform: scale(0.5);
      transform: scale(0.5);
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
