!function(t){var n=Upfront.Settings&&Upfront.Settings.l10n?Upfront.Settings.l10n.global.views:Upfront.mainData.l10n.global.views;upfrontrjs.define(["scripts/upfront/upfront-views-editor/commands/command"],function(t){return t.extend({className:"command-trash sidebar-commands-button light upfront-icon upfront-icon-trash",render:function(){this.listenTo(Upfront.Events,"click:edit:navigate",this.toggle),this.$el.html(n.trash),this.toggle()},toggle:function(t){"undefined"!=typeof t?t===!1?this.$el.hide():this.$el.show():"undefined"==typeof _upfront_post_data||_upfront_post_data.post_id===!1?this.$el.hide():this.$el.show()},on_click:function(){Upfront.Events.trigger("command:layout:trash")}})})}(jQuery);