import Vue from 'vue';
import store from '@/store';
import * as types from '@/store/mutation-types';
import Router from 'vue-router';
import find from './find';
import learning from './learning';
import my from './my';

Vue.use(Router);
const routes = [
  {
    path: '/',
    redirect: '/find',
    name: 'home',
    meta: {
      title: ''
    },
    component: resolve => require(['@/containers/home.vue'], resolve),
    children: [
      ...find,
      ...learning,
      ...my,
      {
        path: '/prelogin',
        name: 'prelogin',
        meta: {
          title: '我的'
        },
        component: resolve => require(['@/containers/login/prelogin.vue'], resolve)
      }
    ]
  }, {
    path: '/login',
    name: 'login',
    meta: {
      title: ''
    },
    component: resolve => require(['@/containers/login/index.vue'], resolve)
  }, {
    path: '/register',
    name: 'register',
    meta: {
      title: ''
    },
    component: resolve => require(['@/containers/register/index.vue'], resolve)
  }, {
    path: '/protocol',
    name: 'protocol',
    meta: {
      title: ''
    },
    component: resolve => require(['@/containers/register/protocol/index.vue'], resolve)
  }, {
    path: '/my/setting',
    name: 'my_setting',
    meta: {
      title: '设置'
    },
    component: resolve => require(['@/containers/my/setting/index.vue'], resolve)
  }, {
    path: '/setting/nickname',
    name: 'setting_nickname',
    meta: {
      title: '昵称设置'
    },
    component: resolve => require(['@/containers/my/setting/nickname.vue'], resolve)
  }, {
    path: '/course/try',
    name: 'course_try',
    component: resolve => require(['@/containers/course/try.vue'], resolve)
  }, {
    path: '/course/web',
    name: 'course_web',
    component: resolve => require(['@/containers/course/detail/web-view.vue'], resolve)
  }, {
    path: '/course/:id',
    name: 'course',
    meta: {
      title: '课程详情'
    },
    component: resolve => require(['@/containers/course/index.vue'], resolve)
  }, {
    path: '/order/:id',
    name: 'order',
    meta: {
      title: '确认订单'
    },
    component: resolve => require(['@/containers/order/index.vue'], resolve)
  }, {
    path: '/more',
    name: 'more',
    meta: {
      title: '所有课程'
    },
    component: resolve => require(['@/containers/more/index.vue'], resolve)
  }
];

// 页面刷新，store数据会被清掉，需对token、user重新赋值
if (localStorage.getItem('token')) {
  store.commit(types.USER_LOGIN, {
    token: localStorage.getItem('token'),
    user: JSON.parse(localStorage.getItem('user'))
  });
}

const router = new Router({
  routes
});

router.beforeEach((to, from, next) => {
  if (!Object.keys(store.state.settings).length) {
    // 获取全局设置
    store.dispatch('getGlobalSettings', { type: 'site' })
      .then(res => {
        console.log(res, '222');
        if (to.name === 'find') {
          to.meta.title = res.name;
        }
        next();
      });
  } else if (['register', 'login', 'protocol', 'find'].includes(to.name)) {
    to.meta.title = store.state.settings.name;
    next();
  } else {
    next();
  }
});
export default router;
