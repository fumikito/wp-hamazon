import React from 'react';
import {FormBase} from "./form-base.jsx";

/* global HamazonEditor: false */

export class FormAmazon extends FormBase {

  constructor(params) {
    super(params);
    this.state.query = '';
    this.selectedOption = 'All';
  }


  buildParams() {
    return {
      query: this.state.query,
      index: this.state.selectedOption,
      page: this.state.curPage,
    }

  }

  onSelectChange(event) {
    this.setState({
      selectedOption: event.target.value,
    });
  }

  onInputChange(event) {
    this.setState({
      page: 1,
      query: event.target.value,
    });
  }

  render() {
    return (
      <div className="hamazon-modal-form-wrapper">
        <div className="hamazon-modal-form">
          <div className="hamazon-modal-form-item">
            <label htmlFor="hamazon-input-amazon-category" className="hamazon-modal-form-label">{HamazonEditor.category}</label>
            <select id="hamazon-input-amazon-category" value={this.state.selectedOption} onChange={(e) => {
              this.onSelectChange(e)
            }}>
              {this.props.service.data.options.map((option) => {
                return <option key={option.key} value={option.key}>{option.label}</option>
              })}
            </select>
          </div>
          <div className="hamazon-modal-form-item input">
            <label htmlFor="hamazon-input-amazon-query" className="hamazon-modal-form-label">{HamazonEditor.searchKeyword}</label>
            <input id="hamazon-input-amazon-query" className="regular-text hamazon-modal-input-text" value={this.state.query}
                   onChange={(e) => this.onInputChange(e)}/>
          </div>
          <div className="hamazon-modal-form-item">
            <button onClick={(e) => {
              this.submitHandler(e)
            }} className="button-primary">{HamazonEditor.search}
            </button>
          </div>
        </div>
        {this.paginate()}
      </div>
    )
  }

}
