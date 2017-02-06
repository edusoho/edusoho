import fs from 'fs';
import config from './webpack.config';
import settings from './settings';
import configFn from 'es-webpack-engine/dist/config';

const options = Object.assign({}, config, settings);
const chokidarWatchAddLog = configFn(options).chokidarWatchAddLog;

fs.exists(chokidarWatchAddLog, function(exists) {
  if (!exists) {
    fs.writeFile(chokidarWatchAddLog,'');
  }
});