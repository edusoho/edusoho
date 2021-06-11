<template>
  <a-modal
    :title="modalInfo.title"
    :visible="visible"
    :okText="isEditor ? '保存' : '新增一栏'"
    @ok="handleOk"
    @cancel="handleCancel"
  >
    <a-form :form="form" >
      <a-form-item
        :label="modalInfo.label"
        :label-col="{ span: 5 }"
        :wrapper-col="{ span: 16 }"
      >
        <a-input
          v-decorator="['title', {
            initialValue: chapterUnitInfo.title,
            rules: [{ required: true, message: '标题不能为空' }]
          }]"
        />
      </a-form-item>
    </a-form>
  </a-modal>
</template>

<script>
import _ from 'lodash';
import { Course } from 'common/vue/service';

export default {
  name: 'AddChapterOrUnitModal',

  props: {
    visible: {
      type: Boolean,
      required: true
    },

    chapterUnitInfo: {
      type: Object,
      required: true
    },

    type: String,

    courseId: {
      type: [Number, String],
      required: true
    }
  },

  data() {
    return {
      form: this.$form.createForm(this, { name: 'add_chapter_or_unit' })
    }
  },

  computed: {
    modalInfo() {
      const types = {
        chapter: '章',
        unit: '节'
      };

      const text = types[this.type];

      return {
        title: `${this.isEditor ? '编辑' : '创建' } ${text}`,
        label: `${text} 标题`
      }
    },

    isEditor() {
      return !!_.size(this.chapterUnitInfo);
    }
  },

  methods: {
    handleOk() {
      this.form.validateFields((err, values) => {
        if (!err) {
          const params = {
            type: this.type,
            title: values.title
          }

          if (this.isEditor) {
            Course.editorChapter(this.courseId, this.chapterUnitInfo.id, params).then(res => {
              this.$emit('change-lesson-directory', { eventType: 'update' });
              this.handleCancel();
            });
          } else {
            Course.addChapter(this.courseId, params).then(res => {
              this.$emit('change-lesson-directory', { addData: [res] });
              this.handleCancel();
            });
          }
        }
      });
    },

    handleCancel() {
      this.$emit('handle-cancel');
      this.form.resetFields();
    }
  }
}
</script>
