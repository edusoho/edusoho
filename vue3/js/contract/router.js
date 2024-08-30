import { createRouter, createWebHashHistory } from 'vue-router';
import ContractPage from './ContractPage.vue'
import CreateContract from './CreateContract.vue';
import UpdateContract from './UpdateContract.vue';

const router = createRouter({
  history: createWebHashHistory(),
  routes: [
    {
      path: '/',
      name: 'Index',
      component: ContractPage,
    },
    {
      path: '/create',
      name: 'CreateContract',
      component: CreateContract,
    },
    {
      path: '/update',
      name: 'UpdateContract',
      component: UpdateContract,
    }
  ]
})

export default router