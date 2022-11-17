<template>
  <div v-if="teachers.length" class="info-right-teacher info-right-box">
    <div class="title">{{ 'goods.show_page.components.teachers'|trans }}</div>
    <div class="teacher-info clearfix" v-for="item in teachers" :key="item.id">
      <a :href="`/user/${item.uuid}`" target="_blank">
          <img class="js-user-card teacher-info__img pull-left"
               :data-user-id="item.id"
               :data-card-url="`/user/${item.uuid}/card/show`"
               :src="item.avatar.large"
               alt=""
          >
      </a>
      <div class="teacher-info__introduce introduce pull-left">
        <p class="introduce__name">{{ item.nickname }}</p>
        <p class="introduce__text">{{ item.title }}</p>
      </div>
    </div>
  </div>
</template>

<script>
  export default {
    props: {
      teachers: {
        type: Array,
        default: function () {
          return []
        }
      }
    }
  }
</script>
