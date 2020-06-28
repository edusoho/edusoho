<template>
  <div class="info-teacher">
    <div class="info-teacher__item clearfix" v-for="count in 10" :key="count">
      <div class="pull-left teacher-img">
        <img src="http://devtest.edusoho.cn/files/user/2020/03-25/0928000f297f981427.jpeg" alt="">
      </div>
      <div class="pull-left teacher-info">
        <p class="teacher-info__name">教师名称</p>
        <p class="teacher-info__describe text-overflow">教师介绍教师介绍教师介绍教师介绍教师教师介绍教师介绍教师介绍教师介绍教师介绍教师…介绍教师…</p>
      </div>
    </div>
  </div>
</template>