/*!
 * @deps wp-element, hamazon-i18n
 */

const React = wp.element;
const { __ } = wp.i18n;

class BaseRenderer extends React.Component {

	copy( event, copy ) {
		event.preventDefault();
		window.prompt( __( 'Copy this string.', 'hamazon' ), copy );
	}

	getTitle() {
		return null;
	}

	getCode() {
		return '';
	}

	getPrice() {
		return (
			<div className="hamazon-item-price">
				{ this.props.item.price }
			</div>
		);
	}

	getMeta() {
		return null;
	}

	extraButtons() {
		return null;
	}

	render() {
		return (
			<div className="hamazon-item">
				{ ( () => {
					if ( this.props.item.image ) {
						return (
							<div className="hamazon-item-image">
								<img src={ this.props.item.image }/>
							</div>
						)
					} else {
						return null;
					}
				} )() }
				<div className="hamazon-item-content">
					{ ( () => {
						return this.getTitle()
					} )() }
					{ ( () => {
						return this.getPrice()
					} )() }
					{ ( () => {
						return this.getMeta()
					} )() }
					<div className="hamazon-item-meta">
						<button className="button-primary" onClick={ ( event ) => {
							event.preventDefault();
							this.props.selectHandler( this.getCode() );
						} }>{ __( 'Insert', 'hamazon' ) }</button>
						<button className="button" onClick={ ( e ) => {
							this.copy( e, this.getCode() );
						} }>{ __( 'Copy Code', 'hamazon' ) }</button>
						<a className="button" href={ this.props.item.url } target="_blank" rel='noopener noreferer'>
							{ __( 'View', 'hamazon' ) }
						</a>
						<button className="button" onClick={ ( e ) => {
							this.copy( e, this.props.item.url );
						} }>{ __( 'Copy Link', 'hamazon' ) }</button>
						{ ( () => {
							this.extraButtons()
						} )() }
					</div>
				</div>
			</div>
		);
	}
}

wp.hamazon.BaseRenderer = BaseRenderer;
