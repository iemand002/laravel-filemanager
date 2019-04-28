@if(config('filemanager.jquery_datatables.use') && config('filemanager.jquery_datatables.cdn'))
    <script src="https://cdn.datatables.net/1.10.11/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.10.11/js/dataTables.bootstrap.min.js"></script>
@endif
<script>

    // Preview image
    function preview_image(path) {
        $("#preview-image").attr("src", path);
        $("#modal-image-view").modal("show");
    }

    @if(config('filemanager.jquery_datatables.use'))
        // init data tables plugin
        $(function () {
            $("#uploads-table").DataTable({
                @if(config('app.locale') == 'nl')
                // Load translations
                "language": {
                    "url": "//cdn.datatables.net/plug-ins/9dcbecd42ad/i18n/Dutch.json"
                },
                @endif
                @if(isset($_GET['multi']))
                // Change default order column
                "order": [[1, 'asc']]
                @endif
            });
        });
    @endif

    function getUrlParam(paramName) {
        var reParam = new RegExp('(?:[\?&]|&)' + paramName + '=([^&]+)', 'i');
        var match = window.location.search.match(reParam);
        return (match && match.length > 1) ? match[1] : null;
    }

    $(function () {
        var data = {};
        var files = [];
        var saved = false;
        var sent = false;
        data.id = getUrlParam('id');
        data.file = getUrlParam('file');
        data.folder = (getUrlParam('folder') != null) ? getUrlParam('folder') + (getUrlParam('folder') === '/' ? '' : '/') : '/';
        data.cloud = getUrlParam('cloud') != null ? getUrlParam('cloud') : 'local';
        data.webPath = (getUrlParam('cloud') != null ? '{{env('APP_URL')}}' + getUrlParam('cloud') + '/' : '{{config('filesystems.disks.' . config('filesystems.' .  config('filemanager.uploads.storage')) . '.url')}}');

        if (getUrlParam('multi')) {
            var btnMulti = $('#multi-add');

            $('#check-all').click(function (e) {
                var state = this.checked;
                // Iterate each checkbox
                $('input[name="files[]').each(function () {
                    this.checked = state;
                });
                btnMulti.attr('disabled', !state)
            });

            // multi add
            $('input[name="files[]"').change(function (e) {
                if ($('[name="files[]"]:checked').length > 0) {
                    btnMulti.removeAttr('disabled');
                } else {
                    btnMulti.attr('disabled', 'disabled');
                }
            });

            btnMulti.click(function (e) {
                e.preventDefault();
                data.type = 'multi';

                $('[name="files[]"]:checked').each(function () {
                    files.push($(this).data());
                });
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

        $('a.file').click(function (e) {
            // single add
            e.preventDefault();
            data.type = 'single';
            data.files = [$(this).data()];
            saveSocial(data);
        });

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
    })
</script>