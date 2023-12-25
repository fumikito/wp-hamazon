/*!
 * Editor components for Hamazon
 *
 * @handle hamazon-editor
 * @deps wp-element, hamazon-i18n, hamazon-modal
 */

const { createRoot, render, Component } = wp.element;
const { Modal } = wp.hamazon;

/* global HamazonEditor:false */

/**
 * Base component
 */
class HamazonButton extends Component {
	constructor() {
		super();
		this.state = {
			src: HamazonEditor.icon,
			label: HamazonEditor.btnLabel,
			title: HamazonEditor.title,
			show: false,
			services: HamazonEditor.services,
		};
	}

	handleClick() {
		this.setState( { show: true } );
	}

	toggleModal() {
		this.setState( { show: false } );
	}

	render() {
		return (
			<div style={ { display: 'inline-block' } }>
				<button type="button" className="button hamazon-insert-button" onClick={ () => {
					this.handleClick();
				} }>
					<img width="24" height="24" className="hamazon-editor-button" src={ this.state.src } alt="" />
					{ this.state.label }
				</button>
				<Modal show={ this.state.show } onClose={ () => this.toggleModal() } title={ this.state.title }
					services={ this.state.services } codeHandler={ ( code ) => {
						const event = new CustomEvent( 'hamazon', {
							detail: {
								code,
								editor: this.props.editorId,
								target: this.props.target,
							},
						} );
						document.dispatchEvent( event );
						this.toggleModal();
					} } />
			</div>
		);
	}
}

/**
 * Call it all
 */

document.querySelectorAll( '.hamazon-btn-component' ).forEach( ( div ) => {
	if ( createRoot ) {
		createRoot( div ).render( <HamazonButton { ...( div.dataset ) } /> );
	} else {
		render( <HamazonButton { ...( div.dataset ) } />, div );
	}
} );
