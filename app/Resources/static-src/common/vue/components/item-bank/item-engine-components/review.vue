<template>
  <div>
    <!-- 教师批阅组件 -->
    <a-form-item
      class="ibs-review-form-item"
      :label="t('itemEngine.getScore')"
      :label-col="{ span: 2 }"
      :wrapper-col="{ sm: { span: 20 }, xs: { span: 18 } }"
      v-if="reviewType === 'score'"
    >
      <a-input
        style="width: 200px"
        type="number"
        :placeholder="t('itemEngine.reviewPlaceholder')"
        v-decorator="[
          `score${id}`,
          {
            rules: [{ validator: checkScore }],
            initialValue: ''
          }
        ]"
      />
    </a-form-item>
    <a-form-item class="ibs-review-form-item" style="display: none;">
      <a-input
        type="text"
        v-decorator="[
          `mode${id}`,
          {
            initialValue: question.answer_mode
          }
        ]"
      />
    </a-form-item>
    <a-form-item
      class="ibs-review-form-item"
      :label="t('itemReview.review_results')"
      :label-col="{ span: 2 }"
      :wrapper-col="{ sm: { span: 20 }, xs: { span: 18 } }"
      v-if="reviewType !== 'score'"
    >
      <a-radio-group
        name="radioGroup"
        v-decorator="[
          `status${id}`,
          {
            rules: [{ validator: checkRadio }],
            initialValue: ''
          }
        ]"
      >
        <a-radio value="right">
          {{ judgeRightText }}
        </a-radio>
        <a-radio value="wrong">
          {{ judgeErrorText }}
        </a-radio>
      </a-radio-group>
    </a-form-item>
    <a-form-item
      v-if="role !== 'student'"
      class="ibs-pb24 ibs-mt16 ibs-review-form-item"
      :label="t('itemEngine.comment2')"
      :label-col="{ span: 2 }"
      :wrapper-col="{ sm: { span: 20 }, xs: { span: 18 } }"
    >
      <a-textarea
        :rows="0"
        :data-image-download-url="showCKEditorData.filebrowserImageDownloadUrl"
        v-decorator="[
          `comment${id}`,
          {
            initialValue: ''
          }
        ]"
      />
    </a-form-item>
  </div>
</template>

<script>
import Locale from "common/vue/mixins/locale";
import loadScript from "load-script";

export default {
  name: "review",
  mixins: [Locale],
  data() {
    return {
      comment: ""
    };
  },
  props: {
    id: {
      type: String,
      default: ""
    },
    keys: {
      type: Array,
      default() {
        return [];
      }
    },
    form: {
      type: Object,
      default() {
        return {};
      }
    },
    question: {
      type: Object,
      default() {
        return {};
      }
    },
    needScore: {
      type: Number,
      default() {
        return 1;
      }
    },
    role: {
      type: String,
      default: ""
    },

    reviewType: {
      type: String,
      default: "score"
    }
  },
  inject: ["showCKEditorData"],
  mounted() {
    if (this.role === "student") {
      return;
    }
    this.$nextTick(() => {
      console.log(this.id);
      loadScript(this.showCKEditorData.jqueryPath, err => {
        if (err) {
          console.log(err);
        }
        loadScript(this.showCKEditorData.publicPath, err => {
          if (err) {
            console.log(err);
          }
          // 批阅组件的评语输入
          this.initComment();
        });
      });
    });
  },
  computed: {
    judgeRightText() {
      return this.role === "student"
        ? this.t("itemReview.mastered")
        : this.t("itemReview.right");
    },
    judgeErrorText() {
      return this.role === "student"
        ? this.t("itemReview.not_mastered")
        : this.t("itemReview.wrong");
    }
  },
  methods: {
    // 验证规则，不超过当前分数值，
    checkScore(rule, value, callback) {
      // 获取当前题目分数
      const fractionRule = /^(([1-9]{1}\d{0,2})|([0]{1}))(\.(\d){1})?$/;
      if (value < 0) {
        callback(this.t("itemEngine.ScoreRule.one"));
      } else if (value > Number(this.question.score)) {
        callback(this.t("itemEngine.ScoreRule.two"));
      } else if (!fractionRule.test(value)) {
        callback(this.t("itemEngine.ScoreRule.four"));
      } else {
        callback();
      }
    },
    checkRadio(rule, value, callback) {
      if (!value) {
        callback(this.t("itemEngine.reviewRedioRule"));
      } else {
        callback();
      }
    },
    initComment() {
      this.comment = window.CKEDITOR.replace(
        `essay-comment-form_comment${this.id}`,
        {
          toolbar: "Minimal",
          fileSingleSizeLimit: this.showCKEditorData.fileSingleSizeLimit,
          filebrowserImageUploadUrl: this.showCKEditorData
            .filebrowserImageUploadUrl,
          filebrowserImageDownloadUrl: this.showCKEditorData
            .filebrowserImageDownloadUrl,
          language: this.showCKEditorData.language
        }
      );

      this.comment.on("change", () => {
        const data = this.comment.getData();
        this.form.setFieldsValue({ [`comment${this.id}`]: data });
      });
      this.comment.on("blur", () => {
        const data = this.comment.getData();
        this.form.setFieldsValue({ [`comment${this.id}`]: data });
      });
    }
  }
};
</script>
