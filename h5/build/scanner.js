const fs = require("fs");
var join = require("path").join;

const basePath = "./public/static/images/graphic/list";

function getIconList(path) {
  let fileList = [];
  function getJsonFiles(jsonPath) {
    let files = [];
    function findJsonFile(path, jsonFiles) {
      let files = fs.readdirSync(path);
      files.forEach(function(item, index) {
        let fPath = join(path, item);
        let stat = fs.statSync(fPath);
        if (stat.isDirectory() === true) {
          const list = [];
          findJsonFile(fPath, list);
          jsonFiles.push(list);
        }
        if (stat.isFile() === true) {
          if (item.indexOf(".DS_Store") < 0) {
            jsonFiles.push(path.replace("public/", "") + "/" + item);
          }
        }
      });
    }
    findJsonFile(jsonPath, files);
    fileList = files;
  }
  getJsonFiles(path);
  return fileList;
}

module.exports = {
  ICON_LIST: getIconList(basePath)
};
