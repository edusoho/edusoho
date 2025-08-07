# App对接数学公式编辑器

数学公式编辑器通过 Webview 嵌入 App。 嵌入地址为： `/assets/libs/math-editor/math-editor.html`。

## 参数

通过 URL Query 的参数方式传入。 

### `formula`

编辑器初始公式，Latex 格式。 示例：`/assets/libs/math-editor/math-editor.html?formula=x^2`。

## 方法

### `getFormula()`

获取编辑器中的公式，Latex 格式。

注意：App 对接的时候要注意，此接口是 `async` 异步的。 Webview 中是这样暴露的接口：

```javascript
window.getFormula = async () => {
  //...
};
```
