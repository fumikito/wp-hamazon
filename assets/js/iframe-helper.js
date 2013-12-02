jQuery(document).ready(function($){
   $('.hamazon-insert').click(function(e){
       e.preventDefault();
       var targetClass = $(this).attr('data-target'),
           shortCode = $(this).parents('td').find(targetClass).val();
       console.log(shortCode, targetClass);
       if(shortCode && window.parent){
           if(!window.parent.tinyMCE.activeEditor){
               window.parent.QTags.insertContent(shortCode);
           }else{
               window.parent.tinyMCE.activeEditor.execCommand( 'mceInsertContent', false, shortCode );
           }
           // ThickBoxを閉じる
           window.parent.tb_remove();
       }
   });
});