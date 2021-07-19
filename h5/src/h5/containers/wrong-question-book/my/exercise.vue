<template>
  <div>
    <e-loading v-if="isLoading" />
    <div class="wrong-list" style="padding-top: 16px;">
      <div
        class="list-item"
        v-for="(exercise, index) in exerciseList"
        :key="index"
        @click="goToWrongQuestionDetail(exercise.type)"
      >
        <div class="list-item__image">
          <img :src="exercise.cover.middle" />
        </div>
        <div class="list-item__detail">
          <h3 class="title text-overflow">{{ exercise.module }}</h3>
          <p class="number text-overflow">{{ exercise.wrong_number }}道题</p>
        </div>
      </div>

      <empty-course
        v-if="!exerciseList.length && !isLoading"
        :has-button="false"
        text="暂无错题"
      />
    </div>
  </div>
</template>

<script>
import Api from '@/api';
import { mapMutations } from 'vuex';
import * as types from '@/store/mutation-types';
import EmptyCourse from '@/containers/learning/emptyCourse/emptyCourse.vue';

export default {
  name: 'myWrongQuestionBookExercise',

  components: {
    EmptyCourse,
  },

  data() {
    return {
      isLoading: false,
      exerciseList: [],
      poolId: this.$route.query.id,
      title: this.$route.query.title,
    };
  },

  created() {
    this.setNavbarTitle(this.title);
    this.fetchExerciseDetail();
  },

  methods: {
    ...mapMutations({
      setNavbarTitle: types.SET_NAVBAR_TITLE,
    }),

    fetchExerciseDetail() {
      this.isLoading = true;
      Api.getWrongQuestionExercise({
        query: {
          poolId: this.poolId,
        },
      }).then(res => {
        this.isLoading = false;
        this.exerciseList = res;
      });
    },

    goToWrongQuestionDetail(type) {
      this.$router.push({
        name: 'myWrongQuestionBookDetail',
        params: {
          type: 'exercise',
          id: this.poolId,
        },
        query: {
          title: this.title,
          type: type,
        },
      });
    },
  },
};
</script>
