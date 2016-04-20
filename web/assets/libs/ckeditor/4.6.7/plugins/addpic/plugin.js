// (function () {
//      //Section 1 : 按下自定义按钮时执行的代码
//      var a = {
//          exec: function (editor) {
//              show();
//          }
//      },
//      b = 'addpic';
//      CKEDITOR.plugins.add(b, {
//          init: function (editor) {
//              editor.addCommand(b, a);
//              editor.ui.addButton('addpic', {
//                  label: '图片上传',
//                  icon: this.path + 'link-blog.png',
//                  command: b
//              });
//          }
//      });
//  })();
//  function show() {
//     $("#ele6")[0].click();
// }
// CKEDITOR.plugins.add('addpic', {
//     init: function (editor) {
//         var pluginName = 'addpic';
//         CKEDITOR.dialog.add(pluginName, this.path + 'addpic.js');
//         editor.addCommand(pluginName, new CKEDITOR.dialogCommand(pluginName));
//         editor.ui.addButton(pluginName,
//         {
//             label: '添加图片',
//             command: pluginName,
//             icon: this.path + 'link-blog.png',
//             id:'ele6'
//         });
//     }
// });

CKEDITOR.plugins.add( 'addpic', {
    icons: 'addpic',
    init: function( editor ) {
        editor.addCommand( 'addpic', new CKEDITOR.dialogCommand( 'addDialog' ) );
        editor.ui.addButton( 'addpic', {
            label: 'Insert Abbreviation',
            command: 'addpic',
            toolbar: 'insert'
        });

        CKEDITOR.dialog.add( 'addDialog', this.path + 'dialogs/addpic.js' );
        CKEDITOR.dialog.add( 'addDialog', this.path + 'webuploader/uploader.js' );
    }
});

CKEDITOR . plugins . add (  'imguploader' ,  { 
    init :  function ( editor )  { 
        var pluginDirectory =  this . path ; 
        editor . addContentsCss ( pluginDirectory +  'imguploader.css'  ); 
    } 
}  );
// editor.addCommand( 'insertaddpic', {
//     exec: function( editor ) {
//         var now = new Date();
//         editor.insertHtml( 'The current date and time is: <em>' + now.toString() + '</em>' );
//     }
// });

// editor.ui.addButton( 'uppic', {
//     label: 'Insert addpic',
//     command: 'insertaddpic',
//     toolbar: 'insert'
// });

// CKEDITOR.plugins.add( 'addpic', {
//     icons: this.path + 'link-blog.png',
//     init: function( editor ) {
//         editor.addCommand( 'insertaddpic', {
//             exec: function( editor ) {
//                 var now = new Date();
//                 editor.insertHtml( 'The current date and time is: <em>' + now.toString() + '</em>' );
//             }
//         });
//         editor.ui.addButton( 'uppic', {
//             label: 'Insert pic',
//             command: 'insertaddpic',
//             toolbar: 'insert'
//         });
//     }
// });

// Register the plugin within the editor.
// CKEDITOR.plugins.add( 'addpic', {

// 	// Register the icons. They must match command names.
// 	icons: 'link-blog.png',

// 	// The plugin initialization logic goes inside this method.
// 	init: function( editor ) {

// 		// Define the editor command that inserts a timestamp.
// 		editor.addCommand( 'addpic', {

// 			// Define the function that will be fired when the command is executed.
// 			exec: function( editor ) {
// 				var now = new Date();

// 				// Insert the timestamp into the document.
// 				editor.insertHtml( 'The current date and time is: <em>' + now.toString() + '</em>' );
// 			}
// 		});

// 		// Create the toolbar button that executes the above command.
// 		editor.ui.addButton( 'Timestamp', {
// 			label: 'Insert Timestamp',
// 			command: 'addpic',
// 			toolbar: 'insert'
// 		});
// 	}
// });

