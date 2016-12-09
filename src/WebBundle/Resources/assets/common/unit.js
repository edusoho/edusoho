export const trim = (str,is_global=true)=> {
  let result = str.replace(/(^\s+)|(\s+$)/g,"");
  if(is_global) {
    result = result.replace(/\s/g,"");
  }
  return result;
}