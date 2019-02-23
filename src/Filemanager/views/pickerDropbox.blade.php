@extends(config('filemanager.extend_layout.picker'))
@section('pagetitle')
    {{trans('filemanager::filemanager.file_manager_dropbox')}}
@endsection
@section(config('filemanager.css_section'))
    @if(config('filemanager.jquery_datatables.use')&&config('filemanager.jquery_datatables.cdn'))
        <link href="https://cdn.datatables.net/1.10.11/css/dataTables.bootstrap.min.css " type="text/css"
              rel="stylesheet">
    @endif
    <style>
        .table > tbody > tr > td.checkbox-label, .table > thead > tr > th.checkbox-label {
            padding: 0;
        }
        td.checkbox-label label, th.checkbox-label label {
            padding: 8px;
            margin: 0;
            display: block;
        }
    </style>
@endsection
@section(config('filemanager.content_section'))
    <div class="container-fluid">

        {{-- Top Bar --}}
        <div class="row page-title-row">
            <div class="col-md-6">
                <h3 class="pull-left">{{trans('filemanager::filemanager.file_manager_dropbox')}} </h3>
                <div class="pull-left">
                    <ul class="breadcrumb">
                        <?php $link = route('filemanager.picker') . "?folder=";
                        if (isset($_GET['CKEditor']))
                            $link .= "&CKEditor=my-editor&CKEditorFuncNum=0";
                        if (isset($_GET['id']))
                            $link .= "&id=" . $_GET['id'];
                        if (isset($_GET['file']))
                            $link .= "&file=" . $_GET['file'];
                        ?>
                        <li><a href="{{$link}}">root</a></li>
                        <?php $link = route('filemanager.pickerSocial','dropbox') . "?folder=";
                        if (isset($_GET['CKEditor']))
                            $link .= "&CKEditor=my-editor&CKEditorFuncNum=0";
                        if (isset($_GET['id']))
                            $link .= "&id=" . $_GET['id'];
                        if (isset($_GET['file']))
                            $link .= "&file=" . $_GET['file'];
                        ?>
                        <li><a href="{{$link}}"><i class="fa fa-dropbox"></i> Dropbox</a></li>

                            @for($i=0;$i<sizeof($folder);$i++)
                                @if($folder[$i]!='')
                                    <?php $link = route('filemanager.pickerSocial','dropbox') . "?folder=". substr($folder[$i],1);
                                    if (isset($_GET['CKEditor']))
                                        $link .= "&CKEditor=my-editor&CKEditorFuncNum=0";
                                    if (isset($_GET['id']))
                                        $link .= "&id=" . $_GET['id'];
                                    if (isset($_GET['file']))
                                        $link .= "&file=" . $_GET['file'];
                                    ?>
                                    <i class="right chevron icon divider"></i>
                                    @if($i==sizeof($folder)-1)
                                        <div class="active section">{{$folder_split[$i]}}</div>
                                    @else
                                        <a class="section"
                                           href="{{route('filemanager.pickerSocial','dropbox')}}">{{$folder_split[$i]}}</a>
                                    @endif
                                @endif
                            @endfor
                        {{--<li class="active">{{ $folderName }}</li>--}}
                    </ul>
                </div>
            </div>
            <div class="col-md-6 text-right">

            </div>
        </div>

        <div class="row">
            <div class="col-sm-12">

                <div class="table-responsive">
                    <table id="uploads-table" class="table table-striped table-bordered">
                        <thead>
                        <tr>
                            @if(isset($_GET['multi']))
                                <th data-sortable="false" class="checkbox-label">
                                    <label for="check-all">
                                        <input type="checkbox" id="check-all"><span
                                                class="sr-only">{{trans('filemanager::filemanager.check_all')}}</span>
                                    </label>
                                </th>
                            @endif
                            <th>{{trans('filemanager::filemanager.name')}}</th>
                            <th>{{trans('filemanager::filemanager.type')}}</th>
                            <th>{{trans('filemanager::filemanager.date')}}</th>
                            <th>{{trans('filemanager::filemanager.Size')}}</th>
                            <th data-sortable="false">{{trans('filemanager::filemanager.actions')}}</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($data->entries as $entry)
                            @if(array_key_exists('rev',$entry))
                                <tr>
                                    @if(isset($_GET['multi']))
                                        <td class="checkbox-label">
                                            <label for="check{{$loop->index}}">
                                                <input type="checkbox" name="files[]" id="check{{$loop->index}}"
                                                       data-file-id="{{$entry->id}}" data-file-name="{{ $entry->name }}">
                                                <span class="sr-only">{{trans('filemanager::filemanager.check')}}</span>
                                            </label>
                                        </td>
                                    @endif
                                    <td>
                                        <a class="file" href="#" data-file-id="{{$entry->id}}"
                                           data-file-name="{{ $entry->name }}">
                                            @if (array_key_exists('media_info',$entry)&&$entry->media_info->metadata->{'.tag'}=='photo')
                                                <i class="fa fa-file-image-o fa-lg fa-fw"></i>
                                            @else
                                                <i class="fa fa-file-o fa-lg fa-fw"></i>
                                            @endif
                                            {{ $entry->name }}
                                        </a>
                                    </td>
                                    <td>{{ $mimeType = fileMimeType($entry->path_display) }}</td>
                                    <td>{{ \Carbon\Carbon::createFromTimeString($entry->server_modified)->format('j-M-y g:ia') }}</td>
                                    <td>{{ human_filesize($entry->size) }}</td>
                                    <td>
                                        @if (is_image($mimeType))
                                            <button type="button" class="btn btn-xs btn-success"
                                                    onclick="preview_image('{{route('filemanager.getPicture',['provider'=>'dropbox', $entry->id])}}')">
                                                <i class="fa fa-eye fa-lg"></i>
                                                {{trans('filemanager::filemanager.preview')}}
                                            </button>
                                        @endif
                                    </td>
                                </tr>
                            @else
                                <tr>
                                    @if(isset($_GET['multi']))
                                        <td>&nbsp;</td>
                                    @endif
                                    <td>
                                        <?php $link = route('filemanager.pickerSocial',['dropbox']) . "?folder=" . str_replace('%2F','/',rawurlencode(substr($entry->path_lower,1)));
                                        if (isset($_GET['CKEditor']))
                                            $link .= "&CKEditor=my-editor&CKEditorFuncNum=0";
                                        if (isset($_GET['id']))
                                            $link .= "&id=" . $_GET['id'];
                                        if (isset($_GET['file']))
                                            $link .= "&file=" . $_GET['file'];
                                        if (isset($_GET['multi']))
                                            $link .= "&multi=true";
                                        ?>
                                        <a href="{{$link}}">
                                            <i class="fa fa-folder fa-lg fa-fw"></i>
                                            {{$entry->name}}
                                        </a>
                                    </td>
                                        <td>{{trans('filemanager::filemanager.folder')}}</td>
                                        <td>-</td>
                                        <td>-</td>
                                        <td>-</td>
                                </tr>
                            @endif
                        @endforeach


                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    @include('iemand002/filemanager::_modalView')

@stop

@section(config('filemanager.javascript_section'))
    @if(config('filemanager.jquery_datatables.use')&&config('filemanager.jquery_datatables.cdn'))
        <script src="//cdn.datatables.net/1.10.11/js/jquery.dataTables.min.js"></script>
        <script src="//cdn.datatables.net/1.10.11/js/dataTables.bootstrap.min.js"></script>
    @endif
    <script>

        // Preview image
        function preview_image(path) {
            $("#preview-image").attr("src", path);
            $("#modal-image-view").modal("show");
        }

        @if(config('filemanager.jquery_datatables.use'))
        $(function () {
            $("#uploads-table").DataTable({
                @if(config('app.locale')=='nl')
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