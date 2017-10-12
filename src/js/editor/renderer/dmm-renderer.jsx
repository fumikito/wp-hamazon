import React from 'react';
import {AmazonRenderer} from "./amazon-renderer.jsx";

/* global HamazonEditor:false */

export class DmmRenderer extends AmazonRenderer{

  getCode(){
    return '[dmm site="' + this.props.item.site + '" id="' + this.props.item.asin + '"][/dmm]';
  }

  getMeta(){
    let credits = [
        this.props.item.attributes.genre,
        this.props.item.attributes.maker,
        this.props.item.attributes.manufacture,
        this.props.item.attributes.author,
    ];
    return(
      <div className="hamazon-item-creator">
        {credits.map((string, index) => {
          if(string){
            let className = 'hamazon-item-meta-string-' + index;
            const vars = string.map((v) => {
              return v.name;
            }).join(', ');
            return <p key={className}>{vars}</p>
          }else{
            return null;
          }
        })}
      </div>
    );
  }

}
