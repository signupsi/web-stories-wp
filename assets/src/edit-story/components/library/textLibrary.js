/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
/**
 * External dependencies
 */
import PropTypes from 'prop-types';

function MediaLibrary( { onInsert } ) {
	return (
		<>
			<button
				onClick={ () => onInsert( 'text', { content: 'Hello', color: 'black', width: 50, height: 20, x: 5, y: 5, rotationAngle: 0 } ) }
			>
				{ __( 'Insert default', 'amp' ) }
			</button>
			<br />
			<button
				onClick={ () => onInsert( 'text', { content: 'Hello <strong>World</strong>', color: 'purple', fontSize: 40, fontFamily: 'Ubuntu', fontWeight: 400, width: 50, height: 20, x: 5, y: 5, rotationAngle: 0 } ) }
			>
				{ __( 'Insert big purple ubuntu', 'amp' ) }
			</button>
		</>
	);
}

MediaLibrary.propTypes = {
	onInsert: PropTypes.func.isRequired,
};

export default MediaLibrary;