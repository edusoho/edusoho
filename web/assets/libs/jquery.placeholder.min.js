/*!
* jQuery Placeholder snippet
*
* This snippet is written for the purpose of supporting the
* HTML5 placeholder attribute on most non-HTML5-compliant browsers.
*
* Usage: Just include it in the code: <script src="jquery.place.holder-1.0.js"></script>
* and include the HTML5 placeholder attribute in your input or textarea tags.
* Note: This script should go after client code, if client code sets field's color.
*
* Date: May 2011
* Author: Otacon (byf1987_at_gmail.com)
* Version: 1.0
* jQuery Version: 1.5
* Changelog: Initial release
* Tested on: Chrome 10.0; IE6 (IETester); IE8 (IETester)
* Known Issues:
*  Placeholder for Password is currently not supported
*/
      
$(function(){
    // -- Constants --
    var PLACE_HOLDER_COLOR = "rgb(169,169,169)"; // "darkGrey" does not work in IE6
    var PLACE_HOLDER_DATA_NAME = "original-font-color";
          
    // -- Util Methods --  
    var getContent = function(element){
        return $(element).val();       
    }
      
    var setContent = function(element, content){
        $(element).val(content);       
    }
          
    var getPlaceholder = function(element){
        return $(element).attr("placeholder");
    }
          
    var isContentEmpty = function(element){
        var content = getContent(element);
        return (content.length === 0) || content == getPlaceholder(element);
    }
              
    var setPlaceholderStyle = function(element){
        $(element).data(PLACE_HOLDER_DATA_NAME, $(element).css("color"));
        $(element).css("color", PLACE_HOLDER_COLOR);       
    }
          
    var clearPlaceholderStyle = function(element){
        $(element).css("color", $(element).data(PLACE_HOLDER_DATA_NAME));      
        $(element).removeData(PLACE_HOLDER_DATA_NAME);
    }
          
    var showPlaceholder = function(element){
        setContent(element, getPlaceholder(element));
        setPlaceholderStyle(element);  
    }
          
    var hidePlaceholder = function(element){
        if($(element).data(PLACE_HOLDER_DATA_NAME)){
            setContent(element, "");
            clearPlaceholderStyle(element);
        }
    }
          
    // -- Event Handlers --
    var inputFocused = function(){
        if(isContentEmpty(this)){
            hidePlaceholder(this);     
        }
    }
          
    var inputBlurred = function(){
        if(isContentEmpty(this)){
            showPlaceholder(this);
        }
    }
          
    var parentFormSubmitted = function(){
        if(isContentEmpty(this)){
            hidePlaceholder(this);     
        }  
    }
          
    // -- Bind event to components --
    $("textarea, input[type='text']").each(function(index, element){
        if($(element).attr("placeholder")){
            $(element).focus(inputFocused);
            $(element).blur(inputBlurred);
            $(element).bind("parentformsubmitted", parentFormSubmitted);
                  
            // triggers show place holder on page load
            $(element).trigger("blur");
            // triggers form submitted event on parent form submit
            $(element).parents("form").submit(function(){
                $(element).trigger("parentformsubmitted");
            });
        }
    });
});