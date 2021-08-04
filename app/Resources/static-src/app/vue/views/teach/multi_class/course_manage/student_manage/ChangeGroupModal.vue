<template>
  <a-modal :visible="visible" title="批量变更分组" @cancel="handleCancel" @ok="handleOk">
    <a-form-model ref="form" :model="form" :label-col="{ span: 4 }" :wrapper-col="{ span: 20 }" :rules="rules">
      <a-form-model-item label="选择分组" prop="group">
        <a-select v-model="form.group" show-search placeholder="请选择分组">
          <a-select-option v-for="item in groupList" :key="item.id">
            {{ item.name }}
          </a-select-option>
        </a-select>
      </a-form-model-item>
    </a-form-model>
  </a-modal>
</template>

<script>
import { MultiClassStudent } from "common/vue/service";

export default {
  name: "ChangeGroupModal",
  components: {},
  props: {
    visible: {
      type: Boolean,
      required: true,
      default: false,
    },
    groupList: {
      type: Array,
      require: true,
      default: {},
    },
    selectedStudentIds: {
      type: Array,
      required: true,
      default: {},
    },
    multiClassId: {
      required: true,
      default: 0,
    },
  },
  data() {
    const rules = {
      group: [
        {
          required: true,
          message: "请选择分组",
          trigger: "change",
        },
      ],
    };
    return {
      form: {
        group: undefined,
      },
      rules,
    };
  },

  computed: {},

  created() {},

  methods: {
    handleCancel() {
      this.$emit("handle-cancel");
      this.$refs.form.resetFields();
    },
    handleOk() {
      this.$refs.form
        .validate()
        .then(async () => {
          await MultiClassStudent.editGroup(
            this.multiClassId,
            this.form.group,
            { studentIds: this.selectedStudentIds }
          );
          this.$message.success("分组修改成功！");
          this.handleCancel();
        })
        .catch((error) => {
          this.$message.warning("分组修改失败");
        });
    },
  },
};
</script>
<style  scoped>
</style>