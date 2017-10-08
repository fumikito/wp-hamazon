import React from 'react';


// Services
import {AmazonRenderer} from './renderer/amazon-renderer.jsx';
import {FormAmazon} from './form/form-amazon.jsx';

/* global HamazonEditor:false */

export class SearchBox extends React.Component{

  constructor(props){
    super();
    this.state = {
      loading: false,
      items: [],
    };
  }

  setLoading(isLoading){
    this.setState({
        loading: isLoading
    });
  }

  submitHandler(items){
    this.setState({
      items: items,
    });
  }

  render() {
    let classes = 'hamazon-modal-service';
    if (this.props.active) {
      classes += ' active';
    }
    if ( this.state.loading ) {
      classes += ' loading';
    }
    let index = 0;
    let Renderer = false;
    let SearchForm = false;
    switch ( this.props.service.key ) {
      case 'amazon':
        Renderer = AmazonRenderer;
        SearchForm = FormAmazon;
        break;
    }
    if ( Renderer ) {
      if(this.state.items.length){
        return (
          <div className={classes}>
            <SearchForm submitHandler={(items) => {this.submitHandler(items)}} setLoading={(isLoading) => this.setLoading(isLoading)} service={this.props.service} />
            <div className="hamazon-search-result">
              {this.state.items.map((item) => {
                let itemKey = this.props.service.key + '-' + index;
                index++;
                switch(this.props.service.key){
                  default:
                    return <Renderer key={itemKey} item={item} selectHandler={(code) => {this.props.insertCode(code)}} />;
                    break;
                }
              }, this)}
            </div>
          </div>
        )
      }else{
        return(
          <div className={classes}>
            <SearchForm submitHandler={(items) => {this.submitHandler(items)}} setLoading={(isLoading) => this.setLoading(isLoading)} service={this.props.service} />
            <div className="hamazon-search-result-empty">
              <div className="hamazon-modal-search-result-empty">{HamazonEditor.noResult}</div>
            </div>
          </div>
        )
      }
    } else {
      return <div className={classes}>
        <div className="hamazon-modal-search-result-error">{HamazonEditor.invalid}</div>
      </div>;
    }
  }
}
