import { createApp } from 'vue'
import { Form, Input, Button, Modal, Drawer } from 'ant-design-vue'
import { setCurrentPrimaryColor } from '../common'
import 'vue3/main.less'

import App from './App.vue'
import router from './router'
import { Button as VButton } from 'vant'
import 'vant/lib/button/style/index.js'

const app = createApp(App)

app.use(router)
app.use(Drawer)
app.use(Form)
app.use(Input)
app.use(Button)
app.use(Modal)
app.use(VButton)

setCurrentPrimaryColor(app)

app.mount('#contract-h5')