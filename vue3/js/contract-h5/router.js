import { createRouter, createWebHashHistory } from "vue-router"
import App from './App.vue'
import SignContract from './SignContract.vue'
import ContractDetail from './ContractDetail.vue'

const routes = [
  {
    path: '/',
    component: App,
    children: [
      {
        path: 'sign_contract/:id/:goodsKey',
        component: SignContract
      },
      {
        path: 'contract_detail/:id/:goodsKey',
        component: ContractDetail
      }
    ]
  },
]

const router = createRouter({
  history: createWebHashHistory(),
  routes,
})

export default router;