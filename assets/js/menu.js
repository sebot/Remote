window.ringmenu = (function (window, document, $) {
    let app = {};
  
    app.init = function() {
        // add ring menu
        $('body').append(
            '<div class="remote-ringmenu">' +
                '<div style="display:none;" id="rem-ringmenu">' +
                    '<a href="'+ REMOTE.siteUrl +'/wp-admin/index.php"> ' +
                    '<span class="rem-menu-item dashicons dashicons-dashboard"></span></a>' +
                    '<a id="rem-create-new" href="#"> ' +
                    '<span class="rem-menu-item dashicons dashicons-edit"></span></a>' +
                    '<a href="'+ REMOTE.siteUrl +'/wp-admin/nav-menus.php"> ' +
                    '<span class="rem-menu-item dashicons dashicons-admin-links"></span></a>' +
                    '<a href="'+ REMOTE.siteUrl +'/wp-admin/admin.php?page=remote-set"> ' +
                    '<span class="rem-menu-item dashicons dashicons-admin-generic"></span></a>' +
                    '<a href="'+ REMOTE.siteUrl +'/wp-admin/plugins.php"> ' +
                    '<span class="rem-menu-item dashicons dashicons-admin-plugins"></span></a>' +
                    '<a href="'+ REMOTE.siteUrl +'/wp-admin/site-health.php"> ' +
                    '<span class="rem-menu-item dashicons dashicons-shield"></span></a>' +
                    '<a target="_blank" href="' + REMOTE.postUrl + '"> ' +
                    '<span class="rem-menu-item dashicons dashicons-visibility"></span></a>' +
                '</div>' +
                '<span id="rem-menu-handle" class="rem-menu-item dashicons dashicons-image-filter"></span>' +
            '</div>'
        ); 

        // ring menu styles
        app.applyThemeColors('.rem-menu-item');

        $('#rem-menu-handle').click(function(){
            $('#rem-ringmenu').toggle(600);
        });

        $('#rem-create-new').click(function(e){
            e.preventDefault();

            let postTypes = REMOTE.postTypes;
            let output = '';

            for(let i = 0;i < postTypes.length;i++) {
                let postType = postTypes[i];
                let labelText = app.ucFirst(postTypes[i].replaceAll('-', ' '));
                output += '<a style="text-decoration:none;" href="/wp-admin/post-new.php?post_type='+ postType +'" title="create new "'+ labelText +'>' +
                '<div style="color: ' + REMOTE.adminTextColor + ';background-color:' + REMOTE.adminTextColor + 
                ';" class="select-area" data-type="post"><h2>'+ labelText +'</h2></div></a>';
            }

            $('#rem-ringmenu').toggle(600);
            $('#remote-modal').css({'background-color': app.hex2rgba(REMOTE.adminBgColor, 0.8)});
            $('#remote-modal .modal-content').html(
                '<div class="post-create-container">' +
                    output +
                '</div>'
            )

            app.applyThemeColors('.select-area');
            $('#remote-modal').show(600);
        });
    }

    app.applyThemeColors = function(selector){
        $(selector).css({
            'background-color': REMOTE.adminTextColor,
            'color': REMOTE.adminBgColor,
            'border': '1px solid ' + REMOTE.adminTextColor
        });
        $(selector).mouseover(function() {
            $(this).css({
                'background-color': REMOTE.adminBgColor,
                'color': REMOTE.adminTextColor,
                'border': '1px solid ' + REMOTE.adminTextColor
            });
        }).mouseout(function() {
            $(this).css({
                'background-color': REMOTE.adminTextColor,
                'color': REMOTE.adminBgColor
            });
        });
    }

    app.hex2rgba = (hex, alpha = 1) => {
        const [r, g, b] = hex.match(/\w\w/g).map(x => parseInt(x, 16));
        return `rgba(${r},${g},${b},${alpha})`;
    };

    app.ucFirst = function(text) {
        return text.toLowerCase()
        .split(' ')
        .map((s) => s.charAt(0).toUpperCase() + s.substring(1))
        .join(' ');
    };
    
    $(document).ready(app.init);
  
    return app;
})(window, document, jQuery);

String.prototype.replaceAll = function(search, replacement) {
    var target = this;
    return target.split(search).join(replacement);
};
