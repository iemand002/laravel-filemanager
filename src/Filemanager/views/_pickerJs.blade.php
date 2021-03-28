@if(config('filemanager.jquery_datatables.use') && config('filemanager.jquery_datatables.cdn'))
    <script src="https://cdn.datatables.net/1.10.21/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.10.21/js/dataTables.bootstrap4.min.js"></script>
@endif
<script>
    // Preview image
    function preview_image(path) {
        $("#preview-image").attr("src", path);
        $("#modal-image-view").modal("show");
    }

    function getUrlParam(paramName) {
        var reParam = new RegExp('(?:[\?&]|&)' + paramName + '=([^&]+)', 'i');
        var match = window.location.search.match(reParam);
        return (match && match.length > 1) ? match[1] : null;
    }

    $(function () {
        var data = {};
        var files = [];
        data.id = getUrlParam('id');
        data.file = getUrlParam('file');
        data.folder = (getUrlParam('folder') != null) ? getUrlParam('folder') + (getUrlParam('folder') === '/' ? '' : '/') : '/';
        data.cloud = getUrlParam('cloud') != null ? getUrlParam('cloud') : 'local';
        data.webPath = (getUrlParam('cloud') != null ? '{{env('APP_URL')}}' + getUrlParam('cloud') + '/' : '{{config('filesystems.disks.' . config('filesystems.' .  config('filemanager.uploads.storage')) . '.url')}}');

        if (getUrlParam('multi')) {
            var $btnMulti = $('#multi-add');
            var $checkAll = $('#check-all');

            $checkAll.click(function (e) {
                var state = this.checked;
                // Iterate each checkbox
                $('input[name="files[]').each(function () {
                    var checkboxData = $(this).data();
                    this.checked = state;

                    if (state) {
                        if (!files.includes(checkboxData)) {
                            files.push(checkboxData)
                        }
                    } else {
                        files = files.filter(function (item) {
                            return item !== checkboxData;
                        });
                    }
                });
                $btnMulti.attr('disabled', !state);
            });

            function updateButtons() {
                $checkAll.prop('checked', $('input[name="files[]"]').length === $('input[name="files[]"]:checked').length);

                if (files.length > 0) {
                    $btnMulti.removeAttr('disabled');
                } else {
                    $btnMulti.attr('disabled', 'disabled');
                }
            }

            function initMultiFile() {
                updateButtons();
                // multi add
                $('input[name="files[]"]').unbind('change').change(function (e) {
                    var checkboxData = $(this).data();
                    if (files.includes(checkboxData)) {
                        files = files.filter(function (item) {
                            return item !== checkboxData;
                        });
                    } else {
                        files.push(checkboxData)
                    }
                    updateButtons();
                });
            }

            $btnMulti.click(function (e) {
                e.preventDefault();
                data.type = 'multi';
                $btnMulti.attr('disabled', 'disabled');
                data.files = files;
                saveSocial(data);
            })
        }

        function callCkeditor(data) {
            // use CKEditor 3.0 + integration method
            var url = data.webPath + data.folder + data.files[0].fileName;
            if (window.opener) {
                // Popup
                window.opener.CKEDITOR.tools.callFunction(getUrlParam('CKEditorFuncNum'), url);
            } else {
                // Modal (in iframe)
                parent.CKEDITOR.tools.callFunction(getUrlParam('CKEditorFuncNum'), url);
                parent.CKEDITOR.tools.callFunction(getUrlParam('CKEditorCleanUpFuncNum'));

            }
            window.close();
        }

        function initFileClick() {
            $('a.file').click(function (e) {
                // single add
                e.preventDefault();
                data.type = 'single';
                data.files = [$(this).data()];
                saveSocial(data);
            });
        }

        function saveSocial(data) {
            if (data.cloud !== 'local') {
                data._token = "{{ csrf_token() }}";
                $.ajax({
                    type: "POST",
                    url: "{{route('filemanager.save-cloud')}}",
                    data: data
                }).done(function(savedData) {
                    if (getUrlParam('CKEditor')) {
                        callCkeditor(data);
                    } else {
                        for (var i = 0;i < savedData.length; i++){
                            data.files[i].fileId = savedData[i].id;
                        }
                        sentLocalStorage(data);
                    }
                });
            } else {
                if (getUrlParam('CKEditor')) {
                    callCkeditor(data);
                } else {
                    sentLocalStorage(data);
                }
            }
        }

        function sentLocalStorage(data) {
            //https://stackoverflow.com/a/28230846
            localStorage.setItem('fm_data', JSON.stringify(data));
            localStorage.removeItem('fm_data');
            window.close();
        }

        @if(config('filemanager.jquery_datatables.use'))
            // init data tables plugin
            $("#uploads-table").DataTable({
                @if(config('app.locale') == 'nl')
                // Load translations
                "language": {
                    "url": "//cdn.datatables.net/plug-ins/1.10.21/i18n/Dutch.json"
                },
                @endif
                @if(isset($_GET['multi']))
                // Change default order column
                "order": [[1, 'asc']]
                @endif
            }).on('draw', function(){
                initFileClick();
                initMultiFile();
            });
        @else
            initFileClick();
            initMultiFile();
        @endif
    })
</script>