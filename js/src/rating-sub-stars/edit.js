/**
 * WordPress dependencies.
 */
 import ServerSideRender from "@wordpress/server-side-render";
 import {
    useBlockProps,
    ColorPalette,
    InspectorControls,
} from '@wordpress/block-editor';
import { TextControl, ToggleControl, PanelBody, PanelRow } from '@wordpress/components';
import { __ } from '@wordpress/i18n';

 const RatingSubStarsEdit = ( { attributes, setAttributes } ) => (
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
            </PanelBody>
        </InspectorControls>

        <ServerSideRender block="pronamic-reviews-ratings/rating-sub-stars" attributes={ { "showNumber": attributes.showNumber} } />
    </React.Fragment>
 );
 
 export default RatingSubStarsEdit;