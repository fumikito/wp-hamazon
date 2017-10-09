import React from 'react';
import {FormAmazon} from "./form-amazon.jsx";

/* global HamazonEditor: false */

export class FormDmm extends FormAmazon {

  constructor(params) {
    super(params);
    this.state.query = '';
    this.selectedOption = 'DMM.com';
  }

  buildParams() {
    return {
      keyword: this.state.query,
      site: this.state.selectedOption,
      page: this.state.curPage,
    }

  }
}
