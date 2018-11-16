(function($) {

    $.fn.multipleField = function(method) {
        var self = {
            $wrapper: null,

            defaultOptions: {
                id: null,

                template: [],

                nextKey: 1,

                max: null,
                maxReachedMessage: 'Maximum field reached!'
            },

            options: {},

            events: {
                afterInit: 'afterInit',

                beforeAdd: 'beforeAdd',
                afterAdd: 'afterAdd',

                beforeRemove: 'beforeRemove',
                afterRemove: 'afterRemove',

                maxReached: 'maxReached'
            },

            public: {
                add: function() {
                    self.add();
                },

                remove: function(index) {
                    var $item = null;

                    if (index !== undefined) {
                        $item = self.$wrapper.find('.multiple-field-item:eq(' + index + ')');
                    }
                    else {
                        $item = self.$wrapper.find('.multiple-field-item').last();
                    }

                    self.remove($item);
                },

                count: function() {
                    return self.count();
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

                self.$wrapper.on('click', '.js-item-add', function(event) {
                    event.stopPropagation();

                    self.add();

                    return false;
                });

                self.$wrapper.on('click', '.js-item-remove', function(event) {
                    event.stopPropagation();

                    var $item = $(this).closest('.multiple-field-item');

                    self.remove($item);

                    return false;
                });

                self.$wrapper.data('multipleField', self.public);

                var afterInitEvent = $.Event(self.events.afterInit);
                self.$wrapper.trigger(afterInitEvent);
            },

            add: function() {
                if ((self.options.max !== null) && (self.count() > self.options.max)) {
                    var maxReachedEvent = $.Event(self.events.maxReached);

                    self.$wrapper.trigger(maxReachedEvent);

                    if (maxReachedEvent.result !== false) {
                        alert(self.options.maxReachedMessage);
                    }

                    return;
                }

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
