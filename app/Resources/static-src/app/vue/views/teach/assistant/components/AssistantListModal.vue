<template>
  <a-modal
    title="批量修改助教"
    :visible="visible"
    @cancel="handleCancel"
  >
    <!-- Tip: Form表单使用组件FormModel更合适，请大家使用FormModel来做表单开发 -->
    <a-form
      :form="form"
      :label-col="{ span: 4 }"
      :wrapper-col="{ span: 20 }"
    >
      <a-form-item label="选择助教" >
        <a-select
          show-search
          :filter-option="false"
          @popupScroll="assistantScroll"
          @search="handleSearchAssistant"
          v-decorator="['assistantId', {
            initialValue: assistant.initialValue,
            rules: [
              { required: true, message: '请选择助教' }
            ]
          }]"
          placeholder="请选择助教"
        >
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
import _ from 'lodash';
import { Assistant } from 'common/vue/service';

export default {
  props: {
    visible: {
      type: Boolean,
      required: true,
      default: false
    },
    multiClass: {
      type: Object,
      required: true,
      default: {}
    },
    selectedStudentIds: {
      type: Array,
      required: true,
      default: {}
    }
  },

  data() {
    return {
      form: this.$form.createForm(this),
      assistant: {
        list: [],
        title: '',
        flag: true,
        initialValue: [],
        paging: {
          pageSize: 10,
          current: 0
        }
      },
    };
  },
  created() {
    this.fetchAssistants();
  },

  methods: {
    assistantScroll: _.debounce(function (e) {
      const { scrollHeight, offsetHeight, scrollTop } = e.target;
      const maxScrollTop = scrollHeight - offsetHeight - 20;
      if (maxScrollTop < scrollTop && this.assistant.flag) {
        this.fetchAssistants();
      }
    }, 300),
    fetchAssistants() {
      const { title, paging: { pageSize, current } } = this.assistant;
      const params = {
        limit: pageSize,
        offset: pageSize * current
      };

      if (title) {
        params.nickname = title;
      }

      Assistant.search(params).then(res => {
        this.assistant.paging.current++;
        this.assistant.list = _.concat(this.assistant.list, res.data);
        if (_.size(this.assistant.list) >= res.paging.total) {
          this.assistant.flag = false;
        }
      });
    },
    handleSearchAssistant: _.debounce(function(input) {
      this.assistant = {
        list: [],
        title: input,
        flag: true,
        paging: {
          pageSize: 10,
          current: 0
        }
      };
      this.fetchAssistants();
    }, 200),
    handleCancel() {
      this.$emit('handle-cancel');
      this.form.resetFields();
    },
    async handleSubmit() {
      this.form.validateFields((err, values) => {
        console.log(err);
        if (!err) {
          Assistant.add({
            assistantId: values.assistantId,
            multiClassId: this.multiClass.id,
            studentIds: this.selectedStudentIds
          }).then((res) => {
            this.$message.success('助教修改成功！', 2);
            this.$emit('handle-cancel');
            window.location.reload();
          }).catch(err => {
            this.$message.warning('助教修改失败', 2);
          });
        }
      });
    },
  }
}
</script>
