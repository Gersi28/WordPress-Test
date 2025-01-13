/**
 * The scss file used only for the editor (admin) view
 */
import './editor.scss';


/**
 * @see https://developer.wordpress.org/block-editor/reference-guides/packages/packages-i18n/
 */
import {__} from '@wordpress/i18n';

/**
 * Block Editor tools.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/packages/packages-block-editor/
 */
import {
  useBlockProps,
  InspectorControls,
  FontSizePicker,
} from '@wordpress/block-editor';

/**
 * @see https://developer.wordpress.org/block-editor/reference-guides/packages/packages-components/
 */
import {PanelBody} from '@wordpress/components';

/**
 * Component used to display the content that is rendered on the server, in our case posts loop.
 * Since this is a dynamic block, we can only use this to have a preview on the back-end of the front-end.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/packages/packages-server-side-render/
 */
import ServerSideRender from "@wordpress/server-side-render";


/**
 * The edit function describes the structure of your block in the context of the
 * editor. This represents what the editor will render when the block is used.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/block-api/block-edit-save/#edit
 *
 * @return {Element} Element to render.
 */
export default function Edit({attributes, setAttributes}) {
  const {
    fontSize,
    backgroundColor
  } = attributes;

  const blockProps = useBlockProps({
    style: {
      fontSize: fontSize + 'px',
      backgroundColor: attributes.style?.color?.gradient
         ? attributes.style.color.gradient
         : attributes.backgroundColor ?? ''
    }
  });

  return (
     <>
       <InspectorControls>
         <PanelBody title={__('Card Settings', 'wordpress-test')}>
           <FontSizePicker
              value={fontSize}
              onChange={(newFontSize) => {
                setAttributes({fontSize: newFontSize});
              }}
           />
         </PanelBody>
       </InspectorControls>

       <div {...blockProps}>
         <ServerSideRender
            block={'wordpress-test/benefits-grid'}
         />
       </div>
     </>
  );
}