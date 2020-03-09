/*!
 * @deps wp-element, hamazon-form-amazon
 */
const React = wp.element;
const { FormAmazon } = wp.hamazon;

class FormDmm extends FormAmazon {

	constructor( params ) {
		super( params );
		this.state.query = '';
		this.selectedOption = 'DMM.com';
	}

	buildParams() {
		return {
			keyword: this.state.query,
			site   : this.state.selectedOption,
			page   : this.state.curPage,
		}
	}
}

wp.hamazon.FormDmm = FormDmm;
