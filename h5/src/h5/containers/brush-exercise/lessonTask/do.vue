<template>
  <div>
    <e-loading v-if="isLoading" />
    <div v-else class="ibs-wap-vue">
      <item-engine
        :answerRecord="answerRecord"
        :assessmentResponse="assessmentResponse"
        :assessment="assessment"
        :answerScene="answerScene"
      ></item-engine>
    </div>
  </div>
</template>

<script>
import Api from '@/api';
const config = {
  assessment: {
    api: 'getAssessmentExerciseRecord',
  },
  chapter: {
    api: 'getChapterExerciseRecord',
  },
};
export default {
  components: {},
  data() {
    return {
      isLoading: true,
      assessment: {},
      answerScene: {},
      answerRecord: {},
      assessmentResponse: {},
    };
  },
  computed: {},
  watch: {},
  created() {
    const type = this.$route.query.type;
    const query = { exerciseId: this.$route.query.exerciseId };
    let data = {};
    if (type === 'assessment') {
      data = {
        assessmentId: this.$route.query.assessmentId,
        moduleId: this.$route.query.moduleId,
      };
    } else {
      data = {
        categoryId: this.$route.query.categoryId,
        moduleId: this.$route.query.moduleId,
      };
    }
    this.getData(query, data, type);
  },
  methods: {
    getData(query, data, type) {
      this.isLoading = true;
      Api[config[type].api]({ query, data }).then(res => {
        this.assessment = res.assessment;
        this.answerScene = res.answer_scene;
        this.answerRecord = res.answer_record;
        this.assessmentResponse = res.assessment_response;
        this.isLoading = false;
      });
    },
  },
};
</script>
