<template>
  <aside-layout :breadcrumbs="[{ name: '参数设置' }]">
    <a-form-model ref="form" :model="form" :rules="rules" :label-col="{ span: 10 }" :wrapper-col="{ span: 10 }" style="max-width: 500px;">
      <a-form-model-item ref="group_number_limit" label="分组学员人数上限" prop="group_number_limit">
        <a-input v-model="form.group_number_limit">
          <span slot="suffix">人</span>
        </a-input>
      </a-form-model-item>
      <a-form-model-item ref="assistant_service_limit" label="助教服务学员人数上限" prop="assistant_service_limit">
        <a-input v-model="form.assistant_service_limit">
          <span slot="suffix">人</span>
        </a-input>
      </a-form-model-item>
      <a-form-model-item ref="review_time_limit" label="超时未批阅时间设定" prop="review_time_limit">
        <a-input v-model="form.review_time_limit">
          <span slot="suffix">小时</span>
        </a-input>
      </a-form-model-item>
    </a-form-model>
    <p class="setup-tip">此处为全局设置，将应用到默认分组大班课，大班课和分组大班课内设置可覆盖全局设置</p>
    <div class="setup-btn">
      <a-space size="large">
        <a-button type="primary" @click="handleSubmit" :loading="ajaxLoading">
          提交
        </a-button>
      </a-space>
    </div>
  </aside-layout>
</template>

<script>
import { MultiClassSetting } from "common/vue/service";
import AsideLayout from "app/vue/views/layouts/aside.vue";

export default {
  name: "index",
  components: {
    AsideLayout,
  },

  data() {
    const rules = {
      group_number_limit: [
        {
          required: true,
          message: "请输入分组学员人数上限",
          trigger: "blur",
        },
        {
          validator: this.validatorGroupNumber,
          trigger: "blur",
        },
      ],
      assistant_service_limit: [
        {
          required: true,
          message: "请输入助教服务学员人数上限",
          trigger: "blur",
        },
        {
          validator: this.validatorAssistantService,
          trigger: "blur",
        },
      ],
      review_time_limit: [
        {
          required: true,
          message: "请输入超时未批阅时间设定",
          trigger: "blur",
        },
        {
          validator: this.validatorReviewTime,
          trigger: "blur",
        },
      ],
    };
    return {
      rules,
      form: {
        group_number_limit: "",
        assistant_service_limit: "",
        review_time_limit: "",
      },
      ajaxLoading: false,
    };
  },

  computed: {},

  mounted() {
    this.getParams();
  },

  methods: {
    async getParams() {
      this.form = await MultiClassSetting.search();
      console.log(this.form);
    },
    validatorGroupNumber(rule, value, callback) {
      if (value > 10000 || value == 0) {
        callback(`人数范围在1-10000人`);
      }
      if (/^\+?[1-9][0-9]*$/.test(value) === false) {
        callback("请输入正整数");
      }
      callback();
    },
    validatorAssistantService(rule, value, callback) {
      if (value > 10000 || value == 0) {
        callback(`人数范围在1-10000人`);
      }
      if (/^\+?[1-9][0-9]*$/.test(value) === false) {
        callback("请输入正整数");
      }
      callback();
    },
    validatorReviewTime(rule, value, callback) {
      if (value > 200) {
        callback(`时间范围在0-200小时`);
      }
      if (/^\+?[0-9][0-9]*$/.test(value) === false) {
        callback("请输入正整数");
      }
      callback();
    },
    handleSubmit() {
      this.$refs.form.validate().then(async () => {
        this.ajaxLoading = true;
        try {
          await MultiClassSetting.add(this.form);
          this.$message.success("保存成功");
        } finally {
          this.ajaxLoading = false;
        }
      });
    },
  },
};
</script>
<style scoped>
.setup-tip {
  margin-left: 48px;
  font-size: 14px;
  color: #999999;
  line-height: 20px;
  font-weight: 400;
}
.setup-btn {
  position: fixed;
  bottom: 0;
  right: 64px;
  left: 200px;
  padding: 12px 0 12px 164px;
  margin: 0;
  border-top: solid 1px #ebebeb;
  background-color: #ffffff;
}
</style>