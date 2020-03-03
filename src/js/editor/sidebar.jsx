import React from 'react';

export function Sidebar(props){
  return (
    <div className="hamazon-modal-sidebar">
      {props.services.map((item) => {
        let itemClassName = 'hamazon-modal-selector';
        if (item.key === props.active) {
          itemClassName += ' active';
        }
        return <div key={item.key} className={itemClassName} onClick={() => {
          props.onSelect(item.key)
        }}>{item.label}</div>
      })}
    </div>
  )
}
