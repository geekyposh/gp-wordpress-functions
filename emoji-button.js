(function() {
    tinymce.PluginManager.add('gp_tc_button', function( editor, url ) {
        editor.addButton( 'gp_tc_button', {
            text: 'emojis',
            type: 'menubutton',
            menu: [
                {
                   	text: 'Smile',
                    icon: 'icon icon-smile',
                    value: '<i class="icon-smile"></i>',
                    onclick: function() {
                       editor.insertContent(this.value());
                    }
                },
                {
                    text: 'Neutral',
                    icon: 'icon icon-neutral',
                    value: '<i class="icon-neutral"></i>',
                    onclick: function() {
                        editor.insertContent(this.value());
                    }
                },
                {
                    text: 'Sad',
                    icon: 'icon icon-sad',
                    value: '<i class="icon-sad"></i>',
                    onclick: function() {
                        editor.insertContent(this.value());
                    }
                }
           ]
        });
    });
})();