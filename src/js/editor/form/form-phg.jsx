/*!
 * @deps wp-element
 */
const React = wp.element;
import { FormBase } from "./form-base.jsx";

/* global HamazonEditor: false */

export class FormPhg extends FormBase {

	constructor( params ) {
		super( params );
		this.state.query = '';
		this.selectedMedia = 'all';
		this.selectedCountry = 'US';
	}


	buildParams() {
		return {
			keyword: this.state.query,
			country: this.state.selectedCountry,
			media  : this.state.selectedMedia,
		}
	}

	onCountryChange( event ) {
		this.setState( {
			selectedCountry: event.target.value,
		} );
	}

	onMediaChange( event ) {
		this.setState( {
			selectedMedia: event.target.value,
		} );
	}

	onInputChange( event ) {
		this.setState( {
			page : 1,
			query: event.target.value,
		} );
	}

	render() {
		return (
			<div className="hamazon-modal-form-wrapper">
				<div className="hamazon-modal-form">
					<div className="hamazon-modal-form-item">
						<label htmlFor="hamazon-input-amazon-category"
							   className="hamazon-modal-form-label">{ HamazonEditor.category }</label>
						<select id="hamazon-input-amazon-category" value={ this.state.selectedMedia }
								onChange={ ( e ) => {
									this.onMediaChange( e );
								} }>
							{ this.props.service.data.media.map( ( option ) => {
								return <option key={ option.key } value={ option.key }>{ option.label }</option>
							} ) }
						</select>
					</div>
					<div className="hamazon-modal-form-item">
						<label htmlFor="hamazon-input-phg-country"
							   className="hamazon-modal-form-label">{ HamazonEditor.countries }</label>
						<select id="hamazon-input-phg-country" value={ this.state.selectedCountry }
								onChange={ ( e ) => {
									this.onCountryChange( e )
								} }>
							{ this.props.service.data.countries.map( ( option ) => {
								return <option key={ option.key } value={ option.key }>{ option.label }</option>
							} ) }
						</select>
					</div>
					<div className="hamazon-modal-form-item input">
						<label htmlFor="hamazon-input-amazon-query"
							   className="hamazon-modal-form-label">{ HamazonEditor.searchKeyword }</label>
						<input id="hamazon-input-amazon-query" className="regular-text hamazon-modal-input-text"
							   value={ this.state.query }
							   onChange={ ( e ) => this.onInputChange( e ) }/>
					</div>
					<div className="hamazon-modal-form-item">
						<button onClick={ ( e ) => {
							this.submitHandler( e )
						} } className="button-primary">{ HamazonEditor.search }
						</button>
					</div>
				</div>
				{ this.paginate() }
			</div>
		)
	}

}
