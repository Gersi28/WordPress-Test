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
  InnerBlocks,
  InspectorControls,
  MediaUpload
} from '@wordpress/block-editor';

/**
 * @see https://developer.wordpress.org/block-editor/reference-guides/packages/packages-components/
 */
import {PanelBody, Button} from '@wordpress/components';

/**
 * The scss file used only for the editor (admin) view
 */
import './editor.scss';

/**
 * Importing data from block.json.
 */
import metadata from './block.json';

export default function Edit({attributes, setAttributes}) {
  const blockProps = useBlockProps({
    className: `${attributes.backgroundImage ? 'has-background-image' : ''} hero-section`,
    style: {
      image: attributes.backgroundImage ?? '',
      backgroundColor: attributes.style?.color?.gradient
         ? attributes.style.color.gradient
         : attributes.backgroundColor ?? undefined
    },
  });

  const TEMPLATE = [
    ['core/heading', {placeholder: 'Enter a heading...'}],
    ['core/paragraph', {placeholder: 'Enter text...'}],
    [
      'core/buttons',
      {},
      [
        ['core/button', {placeholder: 'Button 1'}],
        ['core/button', {placeholder: 'Button 2'}],
      ],
    ],
  ];
  const TEXT_DOMAIN = metadata.textdomain;

  return (
     <>
       <InspectorControls>
         <PanelBody title={__('Background Settings', TEXT_DOMAIN)} initialOpen={true}>
           <p>{__('Background Image', TEXT_DOMAIN)}</p>
           <MediaUpload
              onSelect={(media) => setAttributes({image: media.url})}
              allowedTypes={['image']}
              render={({open}) => (
                 <Button onClick={open} variant="secondary">
                   {__('Choose Image', TEXT_DOMAIN)}
                 </Button>
              )}
           />
           {attributes.backgroundImage && (
              <Button
                 onClick={() => setAttributes({image: ''})}
                 variant="link"
                 isDestructive
              >
                {__('Remove Background Image', TEXT_DOMAIN)}
              </Button>
           )}
         </PanelBody>
       </InspectorControls>
       <div {...blockProps}>
         <InnerBlocks
            allowedBlocks={['core/heading', 'core/paragraph', 'core/buttons']}
            template={TEMPLATE}
            templateLock="all"
         />
       </div>
     </>
  );
}