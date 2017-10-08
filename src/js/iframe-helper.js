/* global tinyMCE:false */

document.addEventListener('hamazon', function (event) {
  'use strict';
  //editorId
  if(tinymce.activeEditor && !tinymce.activeEditor.isHidden()){
    var editor = tinymce.editors[event.detail.editor];
    console.log(editor);
    editor.execCommand('mceInsertContent', false, event.detail.code)
  }else{
    // if not exists, do quicktag
    if(window.parent.QTags){
      window.parent.QTags.insertContent(event.detail.code);
    }
  }

});
