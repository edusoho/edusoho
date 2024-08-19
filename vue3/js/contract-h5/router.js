import { createRouter, createWebHashHistory } from "vue-router"
import App from './App.vue'
import WriteSignature from './WriteSignature.vue'
import SignContract from './SignContract.vue'
import ContractDetail from './ContractDetail.vue'

const routes = [
  {
    path: '/',
    component: App,
    children: [
      {
        path: 'sign',
        component: SignContract
      },
      {
        path: 'contract_detail',
        component: ContractDetail
      },
      {
        path: 'write_signature',
        component: WriteSignature
      }
    ]
  },
]

const router = createRouter({
  history: createWebHashHistory(),
  routes,
})

export default router;