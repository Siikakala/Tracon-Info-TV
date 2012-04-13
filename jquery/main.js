/*
 * jQuery File Upload Plugin JS Example 6.5.1
 * https://github.com/blueimp/jQuery-File-Upload
 *
 * Copyright 2010, Sebastian Tschan
 * https://blueimp.net
 *
 * Licensed under the MIT license:
 * http://www.opensource.org/licenses/MIT
 */

/*jslint nomen: true, unparam: true, regexp: true */
/*global $, window, document */

$(function () {
    'use strict';
    $("#kaikki").button();
    // Initialize the jQuery File Upload widget:
    $('#fileupload').fileupload();
    $('#fileupload').fileupload('option',{
                        url: baseurl+'ajax/upload',
                        redirect: baseurl+'ajax/upload'
                    });
    $('#fileupload').bind('fileuploadstart', function () {
        var widget = $(this),
            progressElement = $('#fileupload-progress').fadeIn(),
            interval = 500,
            total = 0,
            loaded = 0,
            loadedBefore = 0,
            progressTimer,
            progressHandler = function (e, data) {
                loaded = data.loaded;
                total = data.total;
            },
            stopHandler = function () {
                widget
                    .unbind('fileuploadprogressall', progressHandler)
                    .unbind('fileuploadstop', stopHandler);
                window.clearInterval(progressTimer);
                progressElement.fadeOut(function () {
                    progressElement.html('');
                });
            },
            formatTime = function (seconds) {
                var date = new Date(seconds * 1000);
                return ('0' + date.getUTCHours()).slice(-2) + ':' +
                    ('0' + date.getUTCMinutes()).slice(-2) + ':' +
                    ('0' + date.getUTCSeconds()).slice(-2);
            },
            formatBytes = function (bytes) {
                if (bytes >= 1000000000) {
                    return (bytes / 1000000000).toFixed(2) + ' GB';
                }
                if (bytes >= 1000000) {
                    return (bytes / 1000000).toFixed(2) + ' MB';
                }
                if (bytes >= 1000) {
                    return (bytes / 1000).toFixed(2) + ' KB';
                }
                return bytes + ' B';
            },
            formatPercentage = function (floatValue) {
                return (floatValue * 100).toFixed(2) + ' %';
            },
            updateProgressElement = function (loaded, total, bps) {
                progressElement.html(
                    formatBytes(bps) + 'ps | ' +
                        formatTime((total - loaded) / bps) + ' | ' +
                        formatPercentage(loaded / total) + ' | ' +
                        formatBytes(loaded) + ' / ' + formatBytes(total)
                );
            },
            intervalHandler = function () {
                var diff = loaded - loadedBefore;
                if (!diff) {
                    return;
                }
                loadedBefore = loaded;
                updateProgressElement(
                    loaded,
                    total,
                    diff * (1000 / interval)
                );
            };
        widget
            .bind('fileuploadprogressall', progressHandler)
            .bind('fileuploadstop', stopHandler);
        progressTimer = window.setInterval(intervalHandler, interval);
    });

    // Load existing files:
    $('#fileupload').each(function () {
        var that = this;
        $.getJSON(this.action, function (result) {
            if (result) {
                $(that).fileupload('option', 'done')
                    .call(that, null, {result: result});
            }
        });
    });

});
