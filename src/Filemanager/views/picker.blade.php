@extends(config('filemanager.extend_layout.picker'))
@section('pagetitle')
    {{trans('filemanager::filemanager.file_manager')}}
@endsection
@section(config('filemanager.css_section'))
    @if(config('filemanager.jquery_datatables.use')&&config('filemanager.jquery_datatables.cdn'))
        <link href="https://cdn.datatables.net/1.10.11/css/dataTables.bootstrap.min.css " type="text/css"
              rel="stylesheet">
    @endif
@endsection
@section(config('filemanager.content_section'))
    <div class="container-fluid">

        {{-- Top Bar --}}
        <div class="row page-title-row">
            <div class="col-md-6">
                <h3 class="pull-left">{{trans('filemanager::filemanager.uploads')}} </h3>
                <div class="pull-left">
                    <ul class="breadcrumb">
                        @foreach ($breadcrumbs as $path => $disp)
                            <?php $link = route('filemanager.picker') . "?folder=" . $path;
                            if (isset($_GET['CKEditor']))
                                $link .= "&CKEditor=my-editor&CKEditorFuncNum=0";
                            if (isset($_GET['id']))
                                $link .= "&id=" . $_GET['id'];
                            if (isset($_GET['file']))
                                $link .= "&file=" . $_GET['file'];
                            ?>
                            <li><a href="{{$link}}">{{ $disp }}</a></li>
                        @endforeach
                        <li class="active">{{ $folderName }}</li>
                    </ul>
                </div>
            </div>
            <div class="col-md-6 text-right">
                <button type="button" class="btn btn-success btn-md"
                        data-toggle="modal" data-target="#modal-folder-create">
                    <i class="fa fa-plus-circle"></i> {{trans('filemanager::filemanager.new_folder')}}
                </button>
                <button type="button" class="btn btn-primary btn-md"
                        data-toggle="modal" data-target="#modal-file-upload">
                    <i class="fa fa-upload"></i> {{trans('filemanager::filemanager.upload')}}
                </button>
                @php
                    $params['provider']='dropbox';
                @endphp
                @if(is_dropbox_configured())
                    <a href="{{ route('social.redirect', $params) }}" class="btn btn-dropbox">
                        <i class="fa fa-dropbox"></i>
                        {{trans('filemanager::filemanager.connect_dropbox_btn')}}
                    </a>
                @endif
            </div>
        </div>

        <div class="row">
            <div class="col-sm-12">

                @if(config('filemanager.alert_messages.picker'))
                    @if (Session::has('success'))
                        <div class="alert alert-success">
                            <button type="button" class="close" data-dismiss="alert">×</button>
                            <strong>
                                <i class="fa fa-check-circle fa-lg fa-fw"></i> {{trans('filemanager::filemanager.success')}}
                            </strong>
                            {{ Session::get('success') }}
                        </div>
                    @endif
                    @if($errors->any())
                        <div class="alert alert-danger">
                            <button type="button" class="close" data-dismiss="alert">×</button>
                            <strong>{{trans('filemanager::filemanager.whoops')}}</strong>
                            <ul class="list">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                @endif

                <div class="table-responsive">
                    <table id="uploads-table" class="table table-striped table-bordered">
                        <thead>
                        <tr>
                            <th>{{trans('filemanager::filemanager.name')}}</th>
                            <th>{{trans('filemanager::filemanager.type')}}</th>
                            <th>{{trans('filemanager::filemanager.date')}}</th>
                            <th>{{trans('filemanager::filemanager.Size')}}</th>
                            <th data-sortable="false">{{trans('filemanager::filemanager.actions')}}</th>
                        </tr>
                        </thead>
                        <tbody>

                        @if(is_dropbox_loggedIn())
                            <tr>
                                <td>
                                    <?php $link = route('filemanager.pickerDropbox') . "?folder=";
                                    if (isset($_GET['CKEditor']))
                                        $link .= "&CKEditor=my-editor&CKEditorFuncNum=0";
                                    if (isset($_GET['id']))
                                        $link .= "&id=" . $_GET['id'];
                                    if (isset($_GET['file']))
                                        $link .= "&file=" . $_GET['file'];
                                    ?>
                                    <a href="{{$link}}">
                                        <i class="fa fa-dropbox fa-lg fa-fw"></i>
                                        Dropbox
                                    </a>
                                </td>
                                <td>{{trans('filemanager::filemanager.social')}}</td>
                                <td>-</td>
                                <td>-</td>
                                <td>-</td>
                            </tr>
                        @endif
                        {{-- The Subfolders --}}
                        @foreach ($subfolders as $path => $name)
                            <tr>
                                <td>
                                    <?php $link = route('filemanager.picker') . "?folder=" . $path;
                                    if (isset($_GET['CKEditor']))
                                        $link .= "&CKEditor=my-editor&CKEditorFuncNum=0";
                                    if (isset($_GET['id']))
                                        $link .= "&id=" . $_GET['id'];
                                    if (isset($_GET['file']))
                                        $link .= "&file=" . $_GET['file'];
                                    ?>
                                    <a href="{{$link}}">
                                        <i class="fa fa-folder fa-lg fa-fw"></i>
                                        {{ $name }}
                                    </a>
                                </td>
                                <td>{{trans('filemanager::filemanager.folder')}}</td>
                                <td>-</td>
                                <td>-</td>
                                <td>
                                    <button type="button" class="btn btn-xs btn-danger"
                                            onclick="delete_folder('{{ $name }}')">
                                        <i class="fa fa-times-circle fa-lg"></i>
                                        {{trans('filemanager::filemanager.delete')}}
                                    </button>
                                </td>
                            </tr>
                        @endforeach

                        {{-- The Files --}}
                        @foreach ($files as $file)
                            <tr>
                                <td>
                                    <a href="javascript:useFile('{{$file['id']}}','{{ $file['name'] }}')">
                                        @if (is_image($file['mimeType']))
                                            <i class="fa fa-file-image-o fa-lg fa-fw"></i>
                                        @else
                                            <i class="fa fa-file-o fa-lg fa-fw"></i>
                                        @endif
                                        {{ $file['name'] }}
                                    </a>
                                </td>
                                <td>{{ $file['mimeType'] or 'Unknown' }}</td>
                                <td>{{ $file['modified']->format('j-M-y g:ia') }}</td>
                                <td>{{ human_filesize($file['size']) }}</td>
                                <td>
                                    <button type="button" class="btn btn-xs btn-danger"
                                            onclick="delete_file('{{ $file['name'] }}')">
                                        <i class="fa fa-times-circle fa-lg"></i>
                                        {{trans('filemanager::filemanager.delete')}}
                                    </button>
                                    @if (is_image($file['mimeType']))
                                        <button type="button" class="btn btn-xs btn-success"
                                                onclick="preview_image('{{ $file['webPath'] }}')">
                                            <i class="fa fa-eye fa-lg"></i>
                                            {{trans('filemanager::filemanager.preview')}}
                                        </button>
                                    @endif
                                </td>
                            </tr>
                        @endforeach

                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    @include('iemand002/filemanager::_modals')

@stop

@section(config('filemanager.javascript_section'))
    @if(config('filemanager.jquery_datatables.use')&&config('filemanager.jquery_datatables.cdn'))
        <script src="//cdn.datatables.net/1.10.11/js/jquery.dataTables.min.js"></script>
        <script src="//cdn.datatables.net/1.10.11/js/dataTables.bootstrap.min.js"></script>
    @endif
    <script>

        // Confirm file delete
        function delete_file(name) {
            $("#delete-file-name1").html(name);
            $("#delete-file-name2").val(name);
            $("#modal-file-delete").modal("show");
        }

        // Confirm folder delete
        function delete_folder(name) {
            $("#delete-folder-name1").html(name);
            $("#delete-folder-name2").val(name);
            $("#modal-folder-delete").modal("show");
        }

        // Preview image
        function preview_image(path) {
            $("#preview-image").attr("src", path);
            $("#modal-image-view").modal("show");
        }

        @if(config('filemanager.jquery_datatables.use'))
        $(function () {
            $("#uploads-table").DataTable({
                @if(config('app.locale')=='nl')
                "language": {
                    "url": "//cdn.datatables.net/plug-ins/9dcbecd42ad/i18n/Dutch.json"
                }
                @endif
            });
        });

        @endif

        function useFile(id, file) {
            var webpath = '{{config('filesystems.disks.' . config('filesystems.' .  config('filemanager.uploads.storage')) . '.url')}}';
            function getUrlParam(paramName) {
                var reParam = new RegExp('(?:[\?&]|&)' + paramName + '=([^&]+)', 'i');
                var match = window.location.search.match(reParam);
                return (match && match.length > 1) ? match[1] : null;
            }

            if (window.opener || getUrlParam('CKEditor')) {

                var folder = (getUrlParam('folder') != null) ? getUrlParam('folder') + (getUrlParam('folder') === '/' ? '' : '/') : '/';
                if (getUrlParam('CKEditor')) {
                    // use CKEditor 3.0 + integration method
                    if (window.opener) {
                        // Popup
                        window.opener.CKEDITOR.tools.callFunction(getUrlParam('CKEditorFuncNum'), webpath + folder + file);
                    } else {
                        // Modal (in iframe)
                        parent.CKEDITOR.tools.callFunction(getUrlParam('CKEditorFuncNum'), webpath + folder + file);
                        parent.CKEDITOR.tools.callFunction(getUrlParam('CKEditorCleanUpFuncNum'));
                    }
                } else {
                    window.opener.document.getElementById(getUrlParam('id')).value = id;
                    @if(isset($_GET['file']))
                    window.opener.document.getElementById(getUrlParam('file')).value = folder + file;
                    @endif
                    @if(config('filemanager.on_change'))
                    window.opener.document.getElementById(getUrlParam('id')).onchange();
                    @endif
                }

                if (window.opener) {
                    window.close();
                }
            } else {
                $.prompt(lg.fck_select_integration);
            }

            window.close();
        }
    </script>
@stop