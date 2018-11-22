/**
 * Hamazon block.
 *
 * @package hamazon
 */

/* global HamazonBlock:false */

import React from "react";

const { registerBlockType } = wp.blocks;
const { RichText } = wp.editor;
const { SVG, Path, Text, ServerSideRender, Modal, Button } = wp.components;
const { withState } = wp.compose;

import {Sidebar} from "./sidebar";
import {SearchBox} from "./search-box";

const attributes = HamazonBlock.attributes;
attributes.content.source = 'children';
attributes.content.selector = 'p';
const types = HamazonBlock.shortCodes;

registerBlockType( 'hamazon/single', {

  title: HamazonBlock.title,

  description: HamazonBlock.description,

  icon: (
    <SVG viewBox="0 0 1024 1024">
	    <path d="M706.8,767.49h-78.88V476.91c0-35.05-6.57-61.27-19.72-78.65c-13.15-17.37-31.96-26.07-56.44-26.07
		    c-10.58,0-20.48,1.44-29.69,4.31c-9.22,2.88-18.81,7.86-28.79,14.96c-9.97,7.1-20.85,16.7-32.64,28.79
		    c-11.79,12.09-25.39,27.2-40.8,45.33v301.91h-78.88V126.95h78.88v185.41l-2.72,71.62c12.39-14.8,24.55-27.27,36.49-37.4
		    c11.94-10.12,23.87-18.36,35.81-24.71c11.94-6.35,24.1-10.88,36.49-13.6c12.39-2.72,25.23-4.08,38.53-4.08
		    c45.33,0,80.39,13.83,105.17,41.48c24.78,27.65,37.17,69.29,37.17,124.89V767.49z"/>
      <path fill="#FBB03B" d="M123,674.93L94,703c0,0,215.47,228,425,228c226.23,0,371-199,371-199l37,31l3-116l-120,42l52.51,23.38
	      c0,0-133.5,171.3-344.56,171.97C311,885,123,674.93,123,674.93z"/>
    </SVG>
  ),

  category: 'embed',

  attributes: HamazonBlock.attributes,

  edit({attributes, setAttributes}) {

    const { buttonLabel, descPlaceholder, closeLabel, title, services } = HamazonBlock;

    const HamazonModal = withState( {
      isOpen: false,
      activeService: attributes.type,
    } )( ( { isOpen, activeService, setState } ) => {

      const onSelectHandler = ( service ) => {
        setState({activeService: service});
      };

      return (
        <div>
          <button className='hamazon-block-trigger' onClick={ () => setState( { isOpen: true } ) }>{buttonLabel}</button>
          { isOpen ?
            <Modal
              className={'hamazon-block-modal'}
              title={title}
              onRequestClose={ () => setState( { isOpen: false } ) }>
                <div className="hamazon-modal-content">
                  <Sidebar services={services} active={attributes.type} onSelect={onSelectHandler} />
                  <div className="hamazon-modal-search-box">
                    {services.map((service) => {
                      return <SearchBox key={service.key} service={service} active={activeService === service.key} insertCode={(code) => {
                        console.log( code );
                        const match = code.match( /\[([^/].*?)]/ );
                        let shortCode = '';
                        const attrs = {};
                        match[1].split( ' ' ).forEach( ( val, index ) => {
                          if ( ! index ) {
                            shortCode = val;
                          } else {
                            let values = val.split( '=' );
                            const key = ( 'asin' === values[0] ) ? 'id' : values[0];
                            attrs[ key ] = values[1].replace( '"', '' ).replace( '"', '' );
                          }
                        } );
                        attrs.type = types[ shortCode ];
                        setAttributes( attrs );
                      }} />
                    }, this)}
                  </div>
              </div>

              <Button isDefault onClick={ () => setState( { isOpen: false } ) }>
                {closeLabel}
              </Button>
            </Modal>
                   : null }
        </div>
      )
    } );

    function onChangeContent(newContent) {
      setAttributes({content: newContent});
    }

    function onClickButton(){
      alert('hogehoge');
    }

    return (
      <div className='hamazon-block'>
        <div className='hamazon-block-content'>
          <ServerSideRender
            block="hamazon/single"
            attributes={ attributes }
          />
          <div className='hamazon-block-backdrop'>
            <HamazonModal/>
          </div>
        </div>
        <div className='hamazon-block-rich-text'>
          <RichText tagName='p' onChange={onChangeContent} value={attributes.content} placeholder={descPlaceholder}/>
        </div>
      </div>
    );
  },

  save({attributes}) {
    return (
        <RichText.Content
          tagName={'p'}
          value={attributes.content}/>
    );
  },
} );