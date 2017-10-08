import React from 'react';
import {BaseRenderer} from "./base-renderer.jsx";

/* global HamazonEditor:false */

export class AmazonRenderer extends BaseRenderer{

  getCode(){
    return '[tmkm-amazon asin="' + this.props.item.asin + '"][/tmkm-amazon]';
  }

  getTitle(){
    return(
      <h3 className="hamazon-item-title">
        {this.props.item.title}
        <small>{this.props.item.category}</small>
      </h3>
    );
  }

  getMeta(){
    let credits = [
        this.props.item.attributes.Author,
        this.props.item.attributes.Creator,
        this.props.item.attributes.Actor,
        this.props.item.attributes.Director,
        this.props.item.attributes.Manufacturer,
    ];
    return(
      <div className="hamazon-item-creator">
        {credits.map((string, index) => {
          if(string){
            let className = 'hamazon-item-meta-string-' + index;
            return <p key={className}>{string}</p>
          }else{
            return null;
          }
        })}
      </div>
    );
  }

}
