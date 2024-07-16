(function($) {

    $.fn.multipleField = function(method) {
        var self = {
            $wrapper: null,

            defaultOptions: {
                id: null,

                template: [],

                nextKey: 1,
            },

            options: {},

            events: {
                afterInit: 'afterInit',

                beforeAdd: 'beforeAdd',
                afterAdd: 'afterAdd',

                beforeRemove: 'beforeRemove',
                afterRemove: 'afterRemove',
            },

            public: {
                add: function() {
                    self.add();
                },

                remove: function(indexOrElement) {
                    if (indexOrElement instanceof HTMLElement) {
                        indexOrElement = self.public.index(indexOrElement);
                    }

                    var $item = null;

                    if (indexOrElement !== undefined) {
                        $item = self.$wrapper.find('.multiple-field-item:eq(' + indexOrElement + ')');
                    } else {
                        $item = self.$wrapper.find('.multiple-field-item').last();
                    }

                    self.remove($item);
                },

                count: function() {
                    return self.count();
                },

                index: function(element) {
                    if ($(element).is('.multiple-field-item')) {
                        return $(this).index();
                    }

                    return $(element).closest('.multiple-field-item').index();
                },

                option: function(name, value) {
                    value = value || null;

                    if (value === null) {
                        if (!self.options.hasOwnProperty(name)) {
                            $.error('Option "' + name + '" doesn\'t exist!');
                        }

                        return self.options[name];
                    }
                    else {
                        self.options[name] = value;
                    }
                }
            },

            init: function(options) {
                self.options  = $.extend(true, {}, self.defaultOptions, options || {});
                self.$wrapper = $('#' + self.options.id);

                self.$wrapper.data('multipleField', self);

                var afterInitEvent = $.Event(self.events.afterInit);
                self.$wrapper.trigger(afterInitEvent);
            },

            add: function() {
                var template = self.options.template.join(++self.options.nextKey);
                var $item    = $(template);

                var beforeAddEvent = $.Event(self.events.beforeAdd);
                self.$wrapper.trigger(beforeAddEvent, [$item]);

                if (beforeAddEvent.result === false) {
                    return;
                }

                $item.appendTo(self.$wrapper);

                var afterAddEvent = $.Event(self.events.afterAdd);
                self.$wrapper.trigger(afterAddEvent, [$item]);
            },

            remove: function($item) {
                var beforeRemoveEvent = $.Event(self.events.beforeRemove);
                self.$wrapper.trigger(beforeRemoveEvent, [$item]);

                if (beforeRemoveEvent.result === false) {
                    return;
                }

                $item.remove();

                var afterRemoveEvent = $.Event(self.events.afterRemove);
                self.$wrapper.trigger(afterRemoveEvent, [$item]);
            },

            count: function() {
                return self.$wrapper.find('.multiple-field-item').length;
            }
        };

        if (self.public[method]) {
            self = $(this).data('multipleField');

            return self.public[method].apply(this, Array.prototype.slice.call(arguments, 1));
        }
        else if (typeof method === 'object' || !method) {
            return self.init.apply(this, arguments);
        }
        else {
            $.error('Method ' + method + ' doesn\'t exists on jQuery.multipleField');
        }
    };

})(window.jQuery);
