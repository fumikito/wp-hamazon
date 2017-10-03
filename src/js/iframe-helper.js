jQuery(document).ready(function($){
    // ショートコードの挿入
    $('.hamazon-insert').click(function(e){
        e.preventDefault();
        var targetClass = $(this).attr('data-target'),
            shortCode = $(this).parents('td').find(targetClass).val();
        if(shortCode && window.parent){
            if( window.parent.tinyMCE.activeEditor && !window.parent.tinyMCE.activeEditor.isHidden() ){
                // TinyMCEがオープンならそれを使う
                window.parent.tinyMCE.activeEditor.execCommand( 'mceInsertContent', false, shortCode )
            }else{
                // なければQTag
                if(window.parent.QTags){
                    window.parent.QTags.insertContent(shortCode);
                }
            }
            // ThickBoxを閉じる
            window.parent.tb_remove();
        }
    });
    // セレクトボックス
    $('select[name=service]').change(function(){
        var service = $(this).val();
        $(this).nextAll('select').each(function(index, elt){
           if('floor[' + service + ']' == $(elt).attr('name')){
               $(elt).css('display', 'inline').addClass('active');
           }else{
               $(elt).css('display', 'none').removeClass('active');
           }
        });
    });
    $('form.search-dmm').submit(function(e){
       if('' == $(this).find('select[name=service]').val()){
           e.preventDefault();
           alert('ジャンルを選択してください');
       }
    });
});