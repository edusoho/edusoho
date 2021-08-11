<template>
  <editor-qualification
    :user-id="userId"
    :edit-info="info"
  />
</template>

<script>
import { TeacherQualification } from 'common/vue/service';
import EditorQualification from 'app/vue/views/components/Teacher/EditorQualification.vue';

export default {
  name: 'SettingQualification',

  components: {
    EditorQualification
  },

  data() {
    return {
      userId: $('.js-user-id').val(),
      info: {}
    }
  },

  async created() {
    this.info = await TeacherQualification.get({ user_id: this.userId });
  }
}
</script>
