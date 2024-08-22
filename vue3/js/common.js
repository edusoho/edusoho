import dayjs from 'dayjs'
import { floor } from 'lodash-es';

export const trans = (value, options) => {
	return Translator.trans(value, options);
}

export const formatDate = (datetime, format = 'YYYY/MM/DD') => {
  if (!datetime) return '-'

  datetime = datetime.toString()

  if (datetime.length < 10) return '-'

  if (datetime.indexOf('-') > -1 || datetime.indexOf('/') > -1) return datetime

  if (datetime.length === 10) {
    datetime *= 1000
  }

  if (datetime.length === 13) {
    datetime *= 1
  }

  return dayjs(datetime).format(format)
}

export const stopFunc = (e) => {
  e.stopPropagation()
}

export const primaryColors = {
  'default': '#006aff',
  'blue-light': '#4bbbfa',
  'blue': '#0081e6',
  'green-light': '#81d867',
  'green': '#46c37b',
  'orange-light': '#f9b469',
  'orange': '#ff7200',
  'purple-light': '#9e9abd',
  'purple': '#773cec',
  'red-light': '#fd5f56',
  'red': '#cf010e'
}

export const getCurrentPrimaryColor = () => {
  return primaryColors[app.mainColor] || "#006aff"
}

export const setCurrentPrimaryColor = (vueInstance) => {
  const primaryColor = getCurrentPrimaryColor()

  vueInstance.config.globalProperties.$primaryColor = primaryColor

  document
    .getElementsByTagName("html")[0]
    .style.setProperty(`--primary-color`, primaryColor);
}

export const translateHexToRgb = (hex, opacity) => {
  const hexArray = hex.split('')

  hexArray.shift()

  const red = hexArray.splice(0, 2).join('')
  const green = hexArray.splice(0, 2).join('')
  const blue = hexArray.splice(0, 2).join('')

  if (opacity) {
    return `rgba(${parseInt(red, 16)}, ${parseInt(green, 16)}, ${parseInt(blue, 16)}, ${opacity})`
  }

  return `rgb(${parseInt(red, 16)}, ${parseInt(green, 16)}, ${parseInt(blue, 16)})`
}

export const defaultUserAvatar = '/assets/img/default/avatar.png'

export const createStyleTag = (path) => {
  if (!path) return

  const $style = document.createElement('link')

  $style.rel = 'stylesheet'
  $style.href = path

  document.getElementsByTagName('head')[0].appendChild($style)
}

export const createScriptTag = (path) => {
  if (!path) return

  const $script = document.createElement('script')

  $script.src = path

  document.getElementsByTagName('body')[0].appendChild($script)
}

export const getQueryParam = (paramName, url) => {
  url = url || window.location.href;

  const queryString = url.split('?')[1];

  if (!queryString) return ''

  const pairs = queryString.split('&');
  const paramValue = pairs.reduce((result, item) => {
    const [key, value] = item.split('=');
    if (key === paramName) {
      return value;
    }
    return result;
  }, null);

  return paramValue || null;
}

export const isMobileBrowser = () => {
  var userAgent = navigator.userAgent || navigator.vendor || window.opera;  

  // Windows Phone must come first because its UA also contains "Android"  
  if (/windows phone/i.test(userAgent)) {  
      return true;  
  }  

  if (/android/i.test(userAgent)) {  
      return true;  
  }  

  // iOS detection  
  if (/iPad|iPhone|iPod/.test(userAgent) && !window.MSStream) {  
      return true;  
  }  

  return false;  
}

export function generateRandomString(length = 4) {  
  let result = '';  
  const characters = 'abcdefghijklmnopqrstuvwxyz123456789'; 
  const charactersLength = characters.length;  

  for (let i = 0; i < length; i++) {  
      result += characters.charAt(floor(Math.random() * charactersLength));  
  }  

  return result;  
}

export function removeHTMLTagsAndEntities(str) {  
  // 去除HTML标签  
  str = str.replace(/<[^>]*>?/gm, '');  

  // 去除HTML实体（这里只列举了一些常见的，你可能需要添加更多）  
  str = str.replace(/&nbsp;/g, ' '); // 替换&nbsp;为空格  
  str = str.replace(/&lt;/g, '<');  // 替换&lt;为<  
  str = str.replace(/&gt;/g, '>');  // 替换&gt;为>  
  str = str.replace(/&amp;/g, '&'); // 替换&amp;为&  
  str = str.replace(/&quot;/g, '"'); // 替换&quot;为"  

  // 如果你想删除所有HTML实体，可以使用这个函数（但请注意，这可能会删除一些不是HTML实体的&符号）  
  // str = str.replace(/&[^;]+;/g, '');  
  return str;  
} 

export const primaryColorOpacity = (opacity) => {
  return translateHexToRgb(getCurrentPrimaryColor(), opacity)
}
