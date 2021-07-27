<template>
  <e-panel :title="$t('courseLearning.teacherStyle')">
    <template v-for="(item, index) in teacherInfo">
      <div class="teacher-content" :key="index">
        <img :src="item.avatar.large" class="avatar-img" />
        <div class="teacher-cell">
          <span class="dark font-family-PFM">{{ item.nickname }}</span>
          <span v-show="item.title" class="text-content">{{ item.title }}</span>
        </div>
      </div>
    </template>
  </e-panel>
</template>
<script>
export default {
  name: 'Teacher',
  props: ['teacherInfo'],
};
</script>
