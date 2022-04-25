/**
 * WordPress dependencies.
 */
import ServerSideRender from '@wordpress/server-side-render';
import {
   useBlockProps,
   ColorPalette,
   InspectorControls,
} from '@wordpress/block-editor';
import { TextControl, ToggleControl, PanelBody, PanelRow } from '@wordpress/components';
import { __ } from '@wordpress/i18n';

const ReviewAuthorEdit = ( { attributes, setAttributes } ) => (
    <React.Fragment>
        <InspectorControls>
            <PanelBody title={ 'Settings' } initialOpen={ true }>
                <PanelRow>
                    <ToggleControl
                        label={ __( 'Show text', 'pronamic_reviews_ratings' ) }
                        checked={ attributes.showText }
						onChange={ ( toggle ) => setAttributes( { showText: toggle } ) }
                    />
                </PanelRow>
            </PanelBody>
        </InspectorControls>
	    <ServerSideRender block="pronamic-reviews-ratings/review-author" attributes={ { "showText": attributes.showText } } />
    </React.Fragment>
);

export default ReviewAuthorEdit;