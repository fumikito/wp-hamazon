/*!
 * @handle hamaozn-dmm-renderer
 * @deps wp-element, hamazon-i18n, hamazon-amazon-renderer
 */
const React = wp.element;
const { AmazonRenderer } = wp.hamazon;

class DmmRenderer extends AmazonRenderer {
	getCode() {
		return '[dmm site="' + this.props.item.site + '" id="' + this.props.item.asin + '"][/dmm]';
	}

	getMeta() {
		const credits = [
			this.props.item.attributes.genre,
			this.props.item.attributes.maker,
			this.props.item.attributes.manufacture,
			this.props.item.attributes.author,
		];
		return (
			<div className="hamazon-item-creator">
				{ credits.map( ( string, index ) => {
					if ( string ) {
						const className = 'hamazon-item-meta-string-' + index;
						const vars = string.map( ( v ) => {
							return v.name;
						} ).join( ', ' );
						return <p key={ className }>{ vars }</p>;
					}
					return null;
				} ) }
			</div>
		);
	}
}

wp.hamazon.DmmRenderer = DmmRenderer;
