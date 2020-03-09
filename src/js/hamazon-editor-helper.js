/*!
 * @deps hamazon-editor
 */

/* global tinyMCE:false */

document.addEventListener( 'hamazon', function ( event ) {
	// Check editorId and if exists, insert to tinymce.
	if ( ! event.detail.editor ) {
		return;
	}
	if ( tinymce.activeEditor && ! tinymce.activeEditor.isHidden() ) {
		const editor = tinymce.editors[ event.detail.editor ];
		editor.execCommand( 'mceInsertContent', false, event.detail.code )
	} else {
		// if not exists, do quicktag.
		if ( window.parent.QTags ) {
			window.parent.QTags.insertContent( event.detail.code );
		}
	}
} );
