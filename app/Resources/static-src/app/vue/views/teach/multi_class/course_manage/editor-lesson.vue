<template>
  <aside-layout :breadcrumbs="[{ name: '课时设置' }]" style="padding-bottom: 88px;">
    <a-form
      :form="form"
      :label-col="{ span: 4 }"
      :wrapper-col="{ span: 20 }"
      style="max-width: 900px;"
    >
      <a-form-item label="排课">
        <Schedule :course-id="selectedCourseId" />
      </a-form-item>
    </a-form>

     <div class="create-multi-class-btn-group">
      <a-space size="large">
        <a-button type="primary" @click="onClickGoToCourseManage">
          确定
        </a-button>
        <a-button @click="onClickGoToCourseManage">
          取消
        </a-button>
      </a-space>
    </div>
  </aside-layout>
</template>

<script>
import AsideLayout from 'app/vue/views/layouts/aside.vue';
import Schedule from '../create/Schedule.vue';

export default {
  name: 'EditorLesson',

  components: {
    AsideLayout,
    Schedule
  },

  data() {
    return {
      form: this.$form.createForm(this, { name: 'multi_class_editor_lesson' }),
      selectedCourseId: this.$route.params.id
    }
  },

  methods: {
    onClickGoToCourseManage() {
      this.$router.push({
        name: 'MultiClassCourseManage',
        params: {
          id: this.selectedCourseId
        }
      });
    }
  }
}
</script>

<style lang="less">
.create-multi-class-btn-group {
  position: fixed;
  bottom: 0;
  right: 64px;
  left: 200px;
  padding: 24px 0 24px 164px;
  margin: 0;
  border-top: solid 1px #ebebeb;
  background-color: #ffffff;
}
</style>
