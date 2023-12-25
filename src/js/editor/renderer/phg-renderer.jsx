/*!
 * @handle hamazon-phg-renderer
 * @deps wp-element, hamazon-amazon-renderer
 */

const React = wp.element;
const { AmazonRenderer } = wp.hamazon;

export class PhgRenderer extends AmazonRenderer {
	getCode() {
		return '[phg kind="' + this.props.item.category + '" id="' + this.props.item.id + '"][/phg]';
	}

	getMeta() {
		const credits = [
			this.props.item.author,
		];
		return (
			<div className="hamazon-item-creator">
				{ credits.map( ( string, index ) => {
					if ( string ) {
						const className = 'hamazon-item-meta-string-' + index;
						return <p key={ className }>{ string }</p>;
					}
					return null;
				} ) }
			</div>
		);
	}
}

wp.hamazon.PhgRenderer = PhgRenderer;
