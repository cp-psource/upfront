(function($){
    var l10n = Upfront.Settings && Upfront.Settings.l10n
            ? Upfront.Settings.l10n.global.views
            : Upfront.mainData.l10n.global.views
        ;
    define([
        'scripts/upfront/upfront-views-editor/commands/command'
    ], function ( Command ) {

        return Command.extend({
            render: function () {
                this.$el.html(l10n.export_history);
            },
            on_click: function () {
                alert("Check console output");
                console.log("Undo actions:", Upfront.Util.Transient.get_all("undo"));
console.log("Redo actions:", Upfront.Util.Transient.get_all("redo"));
            }
        });


    });
}(jQuery));