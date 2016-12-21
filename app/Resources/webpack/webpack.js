import fs from 'fs';
fs.exists("dirName", function(exists) {
  if (exists) {
    fs.unlink('web/static-dist/dev.lock');
  }
});

import esWebpackEngine from 'es-webpack-engine/dist/build';

import config from './webpack.config';
import settings from './settings';

const options = Object.assign({}, config, settings);

export default esWebpackEngine(options);