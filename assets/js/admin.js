window.remote = (function (window, document, $) {
    let app = {};
  
    app.init = function() {
        if (true != REMOTE.adminBarEnabled) {
            $('#wpadminbar').hide();
        }

        if (true != REMOTE.adminMenuEnabled) {
            $('#adminmenuback, #adminmenuwrap').hide();
        }
        
        if (true == REMOTE.uiEnabled) {
            /*
            if (undefined != REMOTE.logo) {
                $('body').prepend('<img class="backend-logo" src="'+REMOTE.logo+'">');
            }
            */

            $('#wpwrap').css('background-color', REMOTE.adminBgColor);
            $('h1').css('color', REMOTE.adminTextColor);

            // wp primary buttons
            $('h1, h2, h3, h4, h5, h6, p, a').css(
                {
                    'color': REMOTE.adminTextColor
                }
            );
            $('.button-primary').css(
                {
                    'background-color': REMOTE.adminTextColor,
                    'color': REMOTE.adminBgColor,
                    'border': 'none'
                }
            );
            
            $('.button-primary').mouseover(function() {
                $(this).css(
                    {
                        'background-color': REMOTE.adminBgColor,
                        'color': REMOTE.adminTextColor,
                        'border': '1px solid ' + REMOTE.adminTextColor
                    }
                );
    
            }).mouseout(function() {
                $(this).css(
                    {
                        'background-color': REMOTE.adminTextColor,
                        'color': REMOTE.adminBgColor
                    }
                );
            });

            $('body').append(
                '<div id="remote-modal" style="display:none;">' + 
                    '<span style="cursor:pointer;font-size:60px;color: ' + REMOTE.adminTextColor + '" id="remote-m-close" class="dashicons dashicons-no-alt"></span>' +
                    '<div class="modal-content"></div>' + 
                '</div>'
            );

            $('#remote-m-close').click(function(){
                $('#remote-modal .modal-content').html('');
                $('#remote-modal').hide(600);
            });
        }
    }
    
    $(document).ready(app.init);
  
    return app;
})(window, document, jQuery);