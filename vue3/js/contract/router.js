import { createRouter, createWebHashHistory } from 'vue-router';
import ContractPage from './ContractPage.vue'
import EditContract from './EditContract.vue';

const router = createRouter({
  history: createWebHashHistory(),
  routes: [
    {
      path: '/',
      name: 'Index',
      component: ContractPage,
    },
    {
      path: '/edit',
      name: 'EditContract',
      component: EditContract,
    }
  ]
})

export default router