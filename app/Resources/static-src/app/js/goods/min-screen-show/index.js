import Course from './Course';

Vue.config.productionTip = false;

new Vue({
  render: createElement => createElement(Course)
}).$mount('#min-screen-show');