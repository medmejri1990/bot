(function () {
    var Message;
    Message = function (arg) {
        this.text = arg.text, this.message_side = arg.message_side;
        this.draw = function (_this) {
            return function () {
                var $message;
                $message = $($('.message_template').clone().html());
                $message.addClass(_this.message_side).find('.text').html(_this.text);
                $('.messages').append($message);
                return setTimeout(function () {
                    return $message.addClass('appeared');
                }, 0);
            };
        }(this);
        return this;
    };
    $(function () {
        var getMessageText, message_side, sendMessage;
        message_side = 'right';
        getMessageText = function () {
            var $message_input;
            $message_input = $('.message_input');
            return $message_input.val();
        };
        sendMessage = function (text, w) {
            var text2;
            var $messages, message;
            if (text.trim() === '') {
                return;
            }

            $messages = $('.messages');
            if (w) {
                message_side_left = 'left'
                message_side_right = 'right'

                messageleft = new Message({
                    text: text,
                    message_side: message_side_left
                });

                messageleft.draw();
                $messages.animate({ scrollTop: $messages.prop('scrollHeight') }, 300);
            }

            $.ajax({
                url : window.location.href+'conversation',
                type : 'POST',
                data : 'text=' + text,
                success : function(code_html, statut){ // success est toujours en place, bien sûr !
                    text2 = code_html;
                    messageright = new Message({
                        text: text2,
                        message_side: message_side_right
                    });

                    messageright.draw();
                    $messages.animate({ scrollTop: $messages.prop('scrollHeight') }, 300);
                },

                error : function(resultat, statut, erreur){

                }

            });
            $('.message_input').val('');
        };
        $('.send_message').click(function (e) {
             sendMessage(getMessageText(), true);

        });
        $('.message_input').keyup(function (e) {
            if (e.which === 13) {
                return sendMessage(getMessageText(), true);
            }
        });

        $(document).on('click','.current_link', function() {
            sendMessage($(this).html(), false);
        });

    });
}.call(this));