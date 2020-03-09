/*!
 * @deps wp-element, hamazon-i18n
 */

const React = wp.element;

wp.hamazon.Sidebar = ( props ) => {
	return (
		<div className="hamazon-modal-sidebar">
			{ props.services.map( ( item ) => {
				let itemClassName = 'hamazon-modal-selector';
				if ( item.key === props.active ) {
					itemClassName += ' active';
				}
				return <div key={ item.key } className={ itemClassName } onClick={ () => {
					props.onSelect( item.key )
				} }>{ item.label }</div>
			} ) }
		</div>
	)
};
