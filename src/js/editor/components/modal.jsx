/*!
 * @handle hamazon-modal
 * @deps wp-api-fetch, wp-element, hamazon-i18n, hamazon-sidebar, hamazon-search-box
 */

const React = wp.element;
const { Sidebar, SearchBox } = wp.hamazon;

// eslint-disable-file jsx-a11y/no-static-element-interactions
class Modal extends React.Component {
	constructor( props ) {
		super( props );
		this.state = {
			active: props.services[ 0 ].key,
		};
	}

	closeHandler( event ) {
		event.preventDefault();
		event.stopPropagation();
		this.props.onClose();
	}

	setActive( service ) {
		this.setState( {
			active: service,
		} );
	}

	render() {
		if ( ! this.props.show ) {
			return null;
		}

		return (
			<div className="hamazon-backdrop" onClick={ ( event ) => {
				this.closeHandler( event );
			} } tabIndex={ 0 } onKeyDown={ ( event ) => {
				if ( 27 === event.keyCode ) {
					this.closeHandler( event );
				}
			} }>
				<div className="hamazon-modal-body" onClick={ ( event ) => {
					event.stopPropagation();
				} } tabIndex={ 0 } onKeyDown={ ( event ) => {
					event.stopPropagation();
				} }>
					<div className="hamazon-modal-content">
						<Sidebar services={ this.props.services } active={ this.state.active }
							onSelect={ ( service ) => this.setActive( service ) } />
						<div className="hamazon-modal-search-box">
							{ this.props.services.map( ( service ) => (
								<SearchBox key={ service.key } service={ service }
									active={ this.state.active === service.key }
									insertCode={ ( code ) => {
										this.props.codeHandler( code );
									} } />
							) ) }
						</div>
					</div>
					<div className="hamazon-modal-header">
						<span>
							{ this.props.title }
						</span>
						<button onClick={ ( e ) => {
							this.closeHandler( e );
						} }>
							X
						</button>
					</div>
				</div>
			</div>
		);
	}
}

wp.hamazon.Modal = Modal;
