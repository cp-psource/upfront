(function($){
    var l10n = Upfront.Settings && Upfront.Settings.l10n
            ? Upfront.Settings.l10n.global.views
            : Upfront.mainData.l10n.global.views
        ;
    define([
        'scripts/upfront/upfront-views-editor/commands/command'
    ], function ( Command ) {

        return Command.extend({
            "className": "command-save sidebar-commands-button blue",
            render: function () {
                Upfront.Events.on("upfront:save:label", this.update_label, this);
                // this.$el.addClass('upfront-icon upfront-icon-save');
                this.$el.html(l10n.save);
                this.$el.prop("title", l10n.save);
            },
            update_label: function (label) {
                var self = this;
                setTimeout( function () {
                    self.$el.html(label);
                    self.$el.prop("title", label);
                }, 200);
            },
            on_click: function () {
                if (_upfront_post_data.layout.specificity && _upfront_post_data.layout.item && !_upfront_post_data.layout.item.match(/-page/) ) {
                    Upfront.Events.trigger("command:layout:save_as");
                } else {
                    Upfront.Events.trigger("command:layout:save");
                }
            }

        });
    });
}(jQuery));