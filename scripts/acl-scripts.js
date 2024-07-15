jQuery(document).ready(function($) {
    var offset = 0;
    var batchSize = 50;
    var userStart = 0;
    var userEnd = 0;

    function processBatch() {
        $.ajax({
            url: aclAjax.ajax_url,
            method: 'POST',
            data: {
                action: 'acl_apply_roles_batch',
                nonce: aclAjax.nonce,
                offset: offset,
                batch_size: batchSize,
                user_start: userStart,
                user_end: userEnd
            },
            success: function(response) {
                if (response.success) {
                    offset = response.data.offset;
                    $('#acl-progress').append('<p>Processed batch up to user offset: ' + offset + '</p>');
                    if (!response.data.finished) {
                        processBatch();
                    } else {
                        $('#acl-progress').append('<p>All users processed.</p>');
                    }
                } else {
                    $('#acl-progress').append('<p>Error processing batch.</p>');
                }
            }
        });
    }

    $('#acl-form').submit(function(event) {
        event.preventDefault();
        $('#acl-progress').empty();
        offset = 0;
        batchSize = $('#acl-batch-size').val();
        userStart = $('#acl-user-start').val();
        userEnd = $('#acl-user-end').val();
        processBatch();
    });
});
