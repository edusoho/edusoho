const opn = require('opn');
var finalhandler = require('finalhandler');
var http = require('http');
var serveStatic = require('serve-static');
// Serve up public/ftp folder
var serve = serveStatic('mochawesome-report', {'index': ['index.html', 'index.htm', 'mochawesome.html']});

// Create server
var server = http.createServer(function onRequest (req, res) {
  serve(req, res, finalhandler(req, res));
});

// Listen
server.listen(8082);
opn('http://127.0.0.1:8082/', 'chrome');
console.log('Server running at http://127.0.0.1:8082/');