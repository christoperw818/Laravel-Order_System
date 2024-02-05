/*
 * jQuery File Upload Demo
 * https://github.com/blueimp/jQuery-File-Upload
 *
 * Copyright 2010, Sebastian Tschan
 * https://blueimp.net
 *
 * Licensed under the MIT license:
 * https://opensource.org/licenses/MIT
 */

/* global $ */

$(function () {
    'use strict';

    // Initialize the jQuery File Upload widget:
    $('#embroidery_fileupload').fileupload({
        // Uncomment the following to send cross-domain cookies:
        //xhrFields: {withCredentials: true},
        url: '/embroidery-upload'
    });

    // Enable iframe cross-domain access via redirect option:
    $('#embroidery_fileupload').fileupload(
        'option',
        'redirect',
        window.location.href.replace(/\/[^/]*$/, '/cors/result.html?%s')
    );

    if (window.location.hostname === 'blueimp.github.io') {
        // Demo settings:
        $('#embroidery_fileupload').fileupload('option', {
            url: '//jquery-file-upload.appspot.com/',
            // Enable image resizing, except for Android and Opera,
            // which actually support image resizing, but fail to
            // send Blob objects via XHR requests:
            disableImageResize: /Android(?!.*Chrome)|Opera/.test(
                window.navigator.userAgent
            ),
            maxFileSize: 999000,
            acceptFileTypes: /(\.|\/)(gif|jpe?g|png)$/i
        });
        // Upload server status check for browsers with CORS support:
        if ($.support.cors) {
            $.ajax({
                url: '//jquery-file-upload.appspot.com/',
                type: 'HEAD'
            }).fail(function () {
                $('<div class="alert alert-danger"></div>')
                    .text('Upload server currently unavailable - ' + new Date())
                    .appendTo('#embroidery_fileupload');
            });
        }
    } else {
        // Load existing files:
        $('#embroidery_fileupload').addClass('fileupload-processing');
        $.ajax({
            // Uncomment the following to send cross-domain cookies:
            //xhrFields: {withCredentials: true},
            url: $('#embroidery_fileupload').fileupload('option', 'url'),
            dataType: 'json',
            context: $('#embroidery_fileupload')[0],
        })
            .always(function () {
                $(this).removeClass('fileupload-processing');
            })
            .done(function (result) {
                $(this)
                    .fileupload('option', 'done')
                    // eslint-disable-next-line new-cap
                    .call(this, $.Event('done'), { result: result });
            });
    }
});
