# pasteimage

## 引入

复制插件目录到 `ckeditor/plugins`

## 配置
```js
CKEDITOR.extraPlugins='pasteimage';
```

## 使用

```js
CKEDITOR.replace('elementId', {
    imagePasteUploadUrl: 'http://xxx.com/image/upload', //upload url
    fileSingleSizeLimit: 2 //image file size (MB)
});
```