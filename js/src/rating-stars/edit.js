/**
 * WordPress dependencies.
 */
 import ServerSideRender from "@wordpress/server-side-render";
 import {
    useBlockProps,
    ColorPalette,
    InspectorControls,
} from '@wordpress/block-editor';
import { TextControl, ToggleControl, PanelBody, PanelRow, SelectControl  } from '@wordpress/components';
import { __ } from '@wordpress/i18n';

let ratingTypes = [ 'Overall rating' ];
fetch( `/${rest_prefix.prefix}/rating-types/all` ).then( res => res.json() ).then ( data => {
    ratingTypes = ratingTypes.concat( Object.keys( data ) );
} );

 const RatingStarsEdit = ( { attributes, setAttributes } ) => (
    <React.Fragment>
        <InspectorControls>
            <PanelBody title={ 'Settings' } initialOpen={ true }>
                <PanelRow>
                    <ToggleControl
                        label={ __( 'Show number', 'pronamic_reviews_ratings' ) }
                        checked={ attributes.showNumber }
						onChange={ ( toggle ) => setAttributes( { showNumber: toggle } ) }
                    />
                </PanelRow>
                <PanelRow>
                    <ToggleControl
                        label={ __( 'Show text', 'pronamic_reviews_ratings' ) }
                        checked={ attributes.showText }
						onChange={ ( toggle ) => setAttributes( { showText: toggle } ) }
                    />
                </PanelRow>
                { attributes.showText &&
                    <PanelRow>
                        <TextControl
                            label={ __( 'Display text', 'pronamic_reviews_ratings' ) }
                            value={ attributes.displayText }
                            type={ "text" }
                            onChange={ ( text ) => setAttributes( { displayText: text } ) }
                        />
                    </PanelRow>
                }
                <SelectControl
                    label="Beoordelingstype"
                    value={ attributes.ratingType }
                    options={ 
                        ratingTypes.map( ( ratingType ) => (
                            { label: __( ratingType, 'pronamic_reviews_ratings' ), value: ratingType }
                        ))
                     }
                    onChange={ ( type ) => setAttributes( { ratingType: type } ) }
                />
            </PanelBody>
        </InspectorControls>

        <ServerSideRender block="pronamic-reviews-ratings/rating-stars" attributes={ { "showNumber": attributes.showNumber, "showText": attributes.showText, "displayText": attributes.displayText } } />
    </React.Fragment>
 );
 
 export default RatingStarsEdit;