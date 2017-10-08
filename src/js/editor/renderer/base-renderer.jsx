import React from 'react';

/* global HamazonEditor:false */

export class BaseRenderer extends React.Component {

  copy(event, copy) {
    event.preventDefault();
    window.prompt('Copy this string.', copy);
  }

  getTitle() {
    return null;
  }

  getCode(){
    return '';
  }

  getPrice() {
    return (
      <div className="hamazon-item-price">
        {this.props.item.price}
      </div>
    );
  }

  getMeta() {
    return null;
  }

  extraButtons(){
    return null;
  }

  render() {
    console.log(this.props.item);
    return (
      <div className="hamazon-item">
        {(()=>{
          if( this.props.item.image ){
            return(
              <div className="hamazon-item-image">
                <img src={this.props.item.image}/>
              </div>
            )
          }else{
            return null;
          }
        })()}
        <div className="hamazon-item-content">
          {(() => {return this.getTitle()})()}
          {(() => {return this.getPrice()})()}
          {(() => {return this.getMeta()})()}
          <div className="hamazon-item-meta">
            <button className="button-primary" onClick={(event) => {
              event.preventDefault();
              this.props.selectHandler(this.getCode());
            }}>{HamazonEditor.insert}</button>
            <button className="button" onClick={(e) => {
              this.copy(e, this.getCode());
            }}>{HamazonEditor.copyCode}</button>
            <a className="button" href={this.props.item.url} target="_blank">
              {HamazonEditor.view}
            </a>
            <button className="button" onClick={(e) => {
              this.copy(e, this.props.item.url);
            }}>{HamazonEditor.copyLink}</button>
            {(() => {this.extraButtons()})()}
          </div>
        </div>
      </div>
    );
  }

}
