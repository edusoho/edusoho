import { createApp } from 'vue'
import ContractH5 from './ContractH5.vue'
import { Form, Input, Button } from 'ant-design-vue'
import { Button as VButton } from 'vant'
import { setCurrentPrimaryColor } from '../common'
import 'vue3/main.less'
import 'vant/lib/button/style/index.js'

const app = createApp(ContractH5)

app.use(Form)
app.use(Input)
app.use(Button)
app.use(VButton)

setCurrentPrimaryColor(app)

app.mount('#contract-h5')