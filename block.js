const { registerBlockType } = wp.blocks;
const { CheckboxControl, PanelBody, SelectControl } = wp.components;
const { InspectorControls } = wp.blockEditor;
const { __ } = wp.i18n;

registerBlockType('instrument/search-block', {
    title: __('Instrument Search', 'instrument-search'),
    icon: 'search',
    category: 'widgets',

    edit: function(props) {
        return wp.element.createElement(
            wp.element.Fragment,
            null,
            [
                wp.element.createElement(
                    InspectorControls,
                    null,
                    wp.element.createElement(
                        PanelBody,
                        {
                            title: __('Settings', 'instrument-search'),
                            initialOpen: true
                        },
                        [
                            wp.element.createElement(CheckboxControl, {
                                label: __('Show Category Filter', 'instrument-search'),
                                checked: props.attributes.showCategoryFilter,
                                onChange: (newVal) => props.setAttributes({ showCategoryFilter: newVal })
                            }),
                            wp.element.createElement(SelectControl, {
                                label: __('Filter Style', 'instrument-search'),
                                value: props.attributes.filterStyle,
                                options: [
                                    { label: __('Dropdown', 'instrument-search'), value: 'dropdown' },
                                    { label: __('Chips', 'instrument-search'), value: 'chips' },
                                    { label: __('Checkboxes', 'instrument-search'), value: 'checkboxes' }
                                ],
                                onChange: (newStyle) => props.setAttributes({ filterStyle: newStyle })
                            })
                        ]
                    )
                ),
                wp.element.createElement(
                    'div',
                    { className: props.className },
                    wp.element.createElement('h3', null, __('Instrument Search Block', 'instrument-search')),
                    wp.element.createElement('p', null, __('Settings can be configured in the block sidebar →', 'instrument-search'))
                )
            ]
        );
    },

    save: function() {
        return null;
    }
});
