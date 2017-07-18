/**
 * Astrio Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0).
 * It is available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you are unable to obtain it through the world-wide-web, please send
 * an email to info@astrio.net so we can send you a copy immediately.
 *
 * @category   skin
 * @package    base_default
 * @copyright  Copyright (c) 2010-2017 Astrio Co. (http://astrio.net)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

var AstrioPickPointController = Class.create({

    // Initialises an instance of the class
    initialize: function(options)
    {
        this.$shippingContainer = $(options.shippingContainerId);
        this.shippingPattern = options.shippingPattern;
        this.selectorPattern = options.selectorPattern;
        this.pickpointCode = options.pickpointCode;
        this.pickpointBlockSelector = options.pickpointBlockSelector;
        this.popupLinkSelector = options.popupLinkSelector;
        this.$form = $(options.form);
        this.saveUrl = options.saveUrl;
        this.callback = options.callback;

        Event.on(this.$shippingContainer, 'change', this.shippingPattern, this.onChangeShippingMethod.bind(this));
        Event.on(this.$shippingContainer, 'change', this.selectorPattern, this.onChangeSelector.bind(this));
        Event.on(this.$shippingContainer, 'click', this.popupLinkSelector, this.openPopup.bind(this));
        Event.on(this.$shippingContainer, 'astrio_pickpoint:widget_callback', this.selectorPattern, this.onChangeSelector.bind(this));

        this.initPickPointBlock();
    },

    // Handles shipping method change
    onChangeShippingMethod: function(event, element)
    {
        this.changePickPointBlockVisibility((0 === element.value.indexOf(this.pickpointCode)));
    },

    // Handles selector/postamat change
    onChangeSelector: function(event, element)
    {
        var $input = this.$shippingContainer.down(this.shippingPattern + '[value^="' + this.pickpointCode + '"]');

        if ($input) {

            $input.value = this.pickpointCode + '_' + element.value;

            if (!$input.readAttribute('checked')) {
                $input.writeAttribute('checked', 'checked');
            }

            if (Object.isFunction(this.callback)) {
                this.callback.bind(this).call();
            }
        }
    },

    // Changes PickPoint block visibility
    changePickPointBlockVisibility: function(visible)
    {
        var $element = this.$shippingContainer.down(this.pickpointBlockSelector);

        if ($element) {
            if (visible) {
                $element.show();
            } else {
                $element.hide();
            }
        }
    },

    // Inits PickPoint block
    initPickPointBlock: function()
    {
        var $element = this.$shippingContainer.down(this.shippingPattern + ':checked');
        this.changePickPointBlockVisibility(($element && 0 === $element.value.indexOf(this.pickpointCode)));
    },

    // Opens PickPoint popup widget
    openPopup: function(event)
    {
        Event.stop(event);

        if ('object' === typeof PickPoint) {
            PickPoint.open(this.handlePickPointWidgetCallback.bind(this), this.getPickPointWidgetParams());
        } else if (window.console && window.console.log) {
            console.log('PickPoint widget is undefined!');
        }
    },

    // Processes PickPoint widget callback
    handlePickPointWidgetCallback: function(result)
    {
        this.$shippingContainer.down(this.selectorPattern).setValue(result.id).fire('astrio_pickpoint:widget_callback');
    },

    // Returns PickPoint widget params
    getPickPointWidgetParams: function()
    {
        var result = {};
        var $element = this.$shippingContainer.down(this.selectorPattern);

        if ($element && $element.tagName === 'SELECT') {
            result.postamat_name = $element.value;
            result.city = $element.options[$element.selectedIndex].text.split(",")[0];
        }

        return result;
    }
});
