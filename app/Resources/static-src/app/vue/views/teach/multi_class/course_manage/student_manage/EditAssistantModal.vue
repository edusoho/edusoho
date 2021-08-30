<template>
  <a-modal title="修改助教" :visible="visible" @cancel="handleCancel">
    <!-- Tip: Form表单使用组件FormModel更合适，请大家使用FormModel来做表单开发 -->
    <a-form :form="form" :label-col="{ span: 4 }" :wrapper-col="{ span: 20 }">
      <a-form-item label="选择助教">
        <a-select show-search :filter-option="false" @search="handleSearchAssistant" v-decorator="['assistantId', {
            initialValue: assistant.initialValue,
            rules: [
              { required: true, message: '请选择助教' }
            ]
          }]" placeholder="请选择助教">
          <a-select-option v-for="item in assistant.list" :key="item.id">
            {{ item.nickname }}
          </a-select-option>
        </a-select>
      </a-form-item>

    </a-form>

    <template slot="footer">
      <a-button key="back" @click="handleCancel">
        取消
      </a-button>
      <a-button key="submit" type="primary" @click="handleSubmit">
        确认
      </a-button>
    </template>
  </a-modal>
</template>

<script>
import _ from "lodash";
import { MultiClassAssistant } from "common/vue/service";
import MultiClassGroupAssistant from "common/vue/service/MultiClassGroupAssistant";

export default {
  name: "EditAssistantModal",
  props: {
    visible: {
      type: Boolean,
      required: true,
      default: false,
    },
    multiClass: {
      type: Object,
      required: true,
      default: {},
    },
    groupId: {
      required: true,
      default: 0,
    },
    multiClassId: {
      required: true,
      default: 0,
    },
  },

  data() {
    return {
      form: this.$form.createForm(this),
      assistant: {
        list: [],
        title: "",
        initialValue: [],
      },
    };
  },
  created() {
    this.fetchAssistants();
  },

  methods: {
    async fetchAssistants() {
      const { title } = this.assistant;
      const params = {
        id: this.multiClassId,
      };

      if (title) {
        params.nickname = title;
      }
      const res = await MultiClassAssistant.search(params);
      this.assistant.list = _.concat(this.assistant.list, res);

    },
    handleSearchAssistant: _.debounce(function (input) {
      this.assistant = {
        list: [],
        title: input,
      };
      this.fetchAssistants();
    }, 200),

    handleCancel() {
      this.$emit("handle-cancel");
      this.form.resetFields();
    },

    async handleSubmit() {
      this.form.validateFields(async (err, values) => {
        if (!err) {
          try {
            await MultiClassGroupAssistant.editGroupAssistant({
              query: {
                multiClassId: this.multiClassId,
                assistantId: values.assistantId,
              },
              data: {
                groupIds: [this.groupId],
              },
            });
            this.$message.success("助教修改成功！", 2);
            this.$emit("handle-cancel");
          } catch (error) {
            this.$message.warning("助教修改失败", 2);
          }
        }
      });
    },
  },
};
</script>
