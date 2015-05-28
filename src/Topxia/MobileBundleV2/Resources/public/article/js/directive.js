define(function(require, exports, module) {
    exports.init = function(app){
        app.directive('onContentLoaded', function ($parse) {
            return {
                restrict: 'A',
                compile: function(tElem, tAttrs) {

                    function bindImgClick(imgs) {
                      var imageArray = new Array();
                      for (var i = 0; i < imgs.length; i++) {
                        var img = imgs[i];
                        img.alt = i;
                        imageArray.push(img.src);
                        img.addEventListener('click',
                        function() {
                          navigator.cordovaUtil.showImages(this.alt,imageArray);
                        })
                      }
                    }

                    return { 
                        post: function postLink(scope, element, attributes) { 

                          var ngBindHtmlGetter = $parse(tAttrs.onContentLoaded);
                          var ngBindHtmlWatch = $parse(tAttrs.onContentLoaded, function getStringValue(value) {
                            return (value || '').toString();
                          });
                            scope.$watch(ngBindHtmlWatch, function() {
                                element.html(ngBindHtmlGetter(scope));
                                bindImgClick(element.find("img"));
                            });
                        }  
                    };
                }
            };
        });
    }
    
});