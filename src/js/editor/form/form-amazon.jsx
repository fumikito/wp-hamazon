import React from 'react';
import {FormBase} from "./form-base.jsx";

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
      query: event.target.value,
    });
  }

  render() {
    return (
      <div className="hamazon-modal-form-wrapper">
        <div className="hamazon-modal-form">
          <div className="hamazon-modal-form-item">
            <select value={this.state.selectedOption} onChange={(e) => {
              this.onSelectChange(e)
            }}>
              {this.props.service.data.options.map((option) => {
                return <option key={option.key} value={option.key}>{option.label}</option>
              })}
            </select>
          </div>
          <div className="hamazon-modal-form-item input">
            <input className="regular-text hamazon-modal-input-text" value={this.state.query}
                   onChange={(e) => this.onInputChange(e)}/>
          </div>
          <div className="hamazon-modal-form-item">
            <button onClick={(e) => {
              this.submitHandler(e)
            }} className="button-primary">Submit
            </button>
          </div>
        </div>
        {this.paginate()}
      </div>
    )
  }

}
