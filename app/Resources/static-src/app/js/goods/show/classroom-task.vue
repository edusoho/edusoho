<template>
  <div class="classroom-task">
    <div class="classroom-task__item" v-for="count in 10" :key="count">
      <div class="task-banner">
        <img src="http://try6.edusoho.cn/files/course/2020/04-14/18332003d725680219.jpeg" alt="">
      </div>
      <div class="task-content">
        <p class="task-content__title text-overflow">ThinkCERCA五步批判性思维读写课五步批判性思维读写课五步批判性思维读写字数制…</p>
        <p class="clearfix task-content__plan">
          <span class="pull-left">教学计划：教学计划名称</span>
          <span class="pull-right">共12课时</span>
        </p>
        <div class="clearfix task-content__teacher">
          <div class="pull-left teacher-info">
            <img src="http://try6.edusoho.cn/files/user/2020/06-15/1741586dd258161303.jpg" alt="">
            <span>某某某班主任</span>
          </div>
          <span class="pull-right price-info">129.00</span>
        </div>
      </div>
    </div>
  </div>
</template>