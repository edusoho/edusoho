import { createRouter, createWebHashHistory } from 'vue-router';
import ContractPage from './ContractPage.vue'
import CreateContract from './CreateContract.vue';

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
    }
  ]
})

export default router