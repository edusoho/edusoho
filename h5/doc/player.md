# Edusoho VideoPlayerSDK Doc

### Start

```html
// 引入sdk,支持AMD，CMD，UMD等方式引入。不支持Moudle2.0方式的引入。
<script src="//service-cdn.qiqiuyun.net/js-sdk/video-player/sdk-v1.js"></script>

// 实例化播放器SDK
<script>
  var sdk = new VideoPlayerSDK( options );
</script>
```


### Options List

| option | type | description |
| :-- | :-- | :-- |
| id | String | 要初始化video的id |
| resId | String | 云平台资源唯一id |
| user | Object | 用户的相关信息 |
| playlist | String | 要播放的媒体文件地址，可以接受m3u8格式 |
| videoHeaderLength | Number | 如果视频有片头，则这个参数必传，否则会导致视频字幕显示的时间不准确 |
| inactivityTimeout | Number | 在鼠标没有动作的inactivityTimeout秒后，控制条会自动隐藏。如果值不大于0，控制条不会自动隐藏 |
| disableControlBar | Boolean | 是否禁用控制条 |
| disableProgressBar | Boolean | 进度条是否可以拖动 |
| remeberLastPos | Boolean | 是否记住上次的播放位置 |
| autoplay | Boolean | 初始化完成后是否自动播放 |
| poster | String | 如果视频不是自动播放，那么视频会显示此图片作为封面展示 |
| textTrack | Array | 关于字幕相关参数的对象数组[[Object](#texttrack-options-object),[Object](#texttrack-options-object),[Object](#texttrack-options-object)] |
| controlBar | Object | 关于控制条的配置参数，如：隐藏禁用倍速播放等 |
| fingerprint | [Object](#fingerprint-options-object) | 关于指纹的相关参数 |
| watermark | Object | 关于水印的相关参数 |
| exam | Object | 关于弹题的相关参数 |
| playbackRates | Object 或 Array | 关于倍速播放的相关参数 |
| h5 | Boolean | 是否开启 H5 优先 |
| pluck | [Object](#pluck-options-object) | 关于试看的相关参数 |
| statsInfo | [Object] | 用户的相关信息 |
|  |  |  |

###### example

```js
const options = {
  id: 'video-container',
  playlist : 'http://examples/playlist.m3u8',
  fingerprint : {
    html : '<span style="color:red;font-size:56px;">你好</span>',
    duration : 2000
  },
  ...
  ...
};

const player = new VideoPlayerSDK(options);
```

----

#### user Options Object

用户信息对象的配置选项

| option | type | description |
| :-- | :-- | :-- |
| id | Number | 用户ID |
| isTeacher | Boolean | 用户是否为老师 |
| authToken | String | 用户的token |

----

#### playbackRates Options Object

倍速播放参数对象的配置选项

| option | type | description |
| :-- | :-- | :-- |
| enable | Boolean | 是否开启倍速播放 |
| source | String | 视频源类型:mp4,flv,hls |
| src | String | 视频源地址 |
| rates | String | 视频源地址 |

----

#### fingerprint Options Object

指纹参数对象的配置选项

| option | type | description |
| :-- | :-- | :-- |
| html | String | HTML片段字符串 |
| duration | Number | 指纹间隔多长时间显示一次 |

----

#### pluck Options Object

试看参数对象的配置选项

| option | type | description |
| :-- | :-- | :-- |
| timelimit | Number | 试看时间限制。以second为单位 |
| display | Boolean | 试看时间结束后是否显示文案 |
| text | String | 如果上面的display配置未true,试看时间结束后的显示文案 |

----

#### textTrack Options Object

字母参数对象的配置选项

| option | type | description |
| :-- | :-- | :-- |
| label | String | 字幕标签。（必传） |
| src | String | 字幕文件地址。（必传） |
| default | Boolean | 是否设置为默认字幕 |

----

#### bulletscreen Options Object

弹幕参数对象的配置选项

| option | type | description |
| :-- | :-- | :-- |
| open | Boolean | 默认是否打开弹幕播放 |
| enable | Boolean | 是否允许发送弹幕 |
| disableContent | String | 不允许发送的提示语 |
| bulletRuleLink | String | 弹幕礼仪页面的地址(uri) |

----

#### exam Options Object

弹题参数对象的配置选项

###### exam

| option | type | description |
| :-- | :-- | :-- |
| popupExam | Object | 弹题相关参数 |

###### popupExam

| option | type | description |
| :-- | :-- | :-- |
| config | Object | 弹题的配置 |
| questions | Array | 弹题对象数组 |

###### config

| option | type | description |
| :-- | :-- | :-- |
| mode | Enum: 'strict' 'middle' 'loose' | 设置弹题的模式，严格，中性，松散三种(必填) |

###### questions[index]

| option | type | description |
| :-- | :-- | :-- |
| time | Number | 设置弹题弹出的时间(必填) |
| type | Enum: 'choice','multiChoice','uncertainChoice','judge','completion' | 题目类型(必填) |
| question | String | 题干内容(必填) |
| options | Array | 提供的选项内容(选择题必填) |
| answer | Array | 正确答案(选择题和判断题必填) |
| analysis | String | 题目讲解分析，其中填空题如果此项为空，将把答案作为分析展示 |

###### options[index]

| option | type | description |
| :-- | :-- | :-- |
| option_key | String | 选项前的标号(必填) |
| option_val | String | 选项标号后的内容(必填) |

###### examples:

- [弹题综合应用实例](./example/exam.md)

----

### SDK Method

| Method | paramters | Description |
| :-- | :-- | :-- |
| play() | null | 播放视频 |
| pause() | null | 暂停视频 |
| getCurrentTime() | null | 获取当前播放时间 |
| setCurrentTime(time) | time[Number] | 设置当前播放时间 |
| setExams(exam) | exam[Object] | 设置弹题 |
| requestFullscreen() | null | 进入全屏 |
| destory() | null | 销毁播放器实例 |
| openModal(data) | data[Object] | 打开播放器内部弹框 |
| closeModal() | null | 关闭播放器弹框 |
| disableBulletScreen() | null | 禁用弹幕发送框 |
| enableBulletScreen(user) | user[Object] | 开启播放器弹框 |

###### example

```js
var player = new VideoPlayerSDK({
  idL 'video-example',
  playlist: 'http://www.xxx.com/xxx.m3u8'
});

player.play()  // 播放
player.pause()  // 暂停
player.getCurrentTime()  // 获取当前播放时间
...
...
```

----

###  SDK Event

| EventType | Description |
| :-- | :-- |
| `ready` | 当播放器准备好，马上可以播放时触发回调 |
| `timeupdate` | 当播放时间更新的时候触发回调函数 |
| `ended` | 播放结束后触发 |
| `paused` | 播放被暂停时触发 |
| `playing` | 启动播放时触发 |
| `firstplay` | 第一次播放时触发 |
| `exam.answered` | 当回答问题后触发 |
| `exam.open` | 当弹题框打开的时候触发 |
| `exam.close` | 当弹题框关闭的时候触发 |

###### example

```js
var player = new VideoPlayerSDK({
  id: 'video-example',
  playlist: 'http://www.xxx.com/xxx.m3u8'
});

player.on('ready', () => {
  alert(123);
})  // 当播放器准备好了，弹出警告123.
```

----

###  功能示例代码


#### 传递用户信息

```js
var sdk = new VideoPlayerSDK({
  id: 'video-container',
  playlist : 'http://examples/playlist.m3u8',
  statsInfo: {
    userId : '123',    //学员ID
    userName : '张三' //学员名字
  }
});
```

#### 隐藏进度条元素

```js
var sdk = new VideoPlayerSDK({
  id: 'video-container',
  playlist : 'http://examples/playlist.m3u8',
  controlBar: {
    disableResolutionSwitcher: true, //隐藏清晰度
    disableVolumeButton: true, //隐藏音量
  }
});
```


#### 倍速播放

```js

var sdk = new VideoPlayerSDK({
  id: 'video-container',
  playlist : 'http://examples/playlist.m3u8',
  playbackRates : {
    rates: [1, 1.25, 15]
  }
});

```

#### 内部弹框

##### 参数

| 参数 | 类型 | 必填 | 描述 |
| --- | --- | --- | -- |
| width | Int | （可选） | 设置弹框的宽度 |
| height | Int | （可选） | 设置弹框的高度 |
| timeout | Int | （可选） | 设置多久之后自动关闭 |
| closable | Boolean | （可选） | 是否允许手动关闭 |
| content | String | （必填） | 弹框的内容 |

##### 示例

```js
var sdk = new VideoPlayerSDK({
  id: 'video-container',
  playlist : 'http://examples/playlist.m3u8',
  autoplay: false
});

// 初始化完成之后弹框。
sdk.on('ready', function() {
  sdk.openModal({
    width: 500,   // （可选）设置内部弹框的宽度。
    height: 300,  // （可选）设置内部弹框的高度。
    content: 'xxxxx'    // （必填）支持 html 内容。
  });
});

// 当视频暂停的时候弹出。
sdk.on('paused', function() {
  sdk.openModal({
    width: 500,   // （可选）设置内部弹框的宽度。
    height: 300,  // （可选）设置内部弹框的高度。
    content: '<iframe src="http://www.esdev.com/ad/1">'    // （必填）支持 html 内容。
  });
});

// 当视频开始播放的时候关闭
sdk.on('playing', function() {
  sdk.closeModal();
});

```

