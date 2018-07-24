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
                <h3 class="pull-left">{{trans('filemanager::filemanager.dropbox')}} </h3>
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
                        <?php $link = route('filemanager.pickerDropbox') . "?folder=";
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
                                    <?php $link = route('filemanager.pickerDropbox') . "?folder=". substr($folder[$i],1);
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
                                           href="{{route('filemanager.pickerDropbox')}}">{{$folder_split[$i]}}</a>
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
                                @if(array_key_exists('media_info',$entry)&&$entry->media_info->metadata->{'.tag'}=='photo')
                                    <tr>
                                        <td>
                                            <a href="javascript:useFile('{{$entry->id}}','{{ $entry->name }}')">
                                                @if (array_key_exists('media_info',$entry)&&$entry->media_info->metadata->{'.tag'}=='photo')
                                                    <i class="fa fa-file-image-o fa-lg fa-fw"></i>
                                                @else
                                                    <i class="fa fa-file-o fa-lg fa-fw"></i>
                                                @endif
                                                {{ $entry->name }}
                                            </a>
                                        </td>
                                        <td>{{ array_key_exists('media_info',$entry)&&$entry->media_info->metadata->{'.tag'}=='photo' ? 'image' : 'Unknown' }}</td>
                                        <td>{{ $entry->server_modified }}</td>
                                        <td>{{ human_filesize($entry->size) }}</td>
                                        <td>
                                            @if (array_key_exists('media_info',$entry)&&$entry->media_info->metadata->{'.tag'}=='photo')
                                                <button type="button" class="btn btn-xs btn-success"
                                                        onclick="preview_image('{{route('filemanager.getDropboxPicture',[$entry->id])}}')">
                                                    <i class="fa fa-eye fa-lg"></i>
                                                    {{trans('filemanager::filemanager.preview')}}
                                                </button>
                                            @endif
                                        </td>

                                    </tr>
                                @endif
                            @else
                                {{--<li class="eight wide tablet six wide computer column">--}}
                                    {{--<a href="{{route('picture.import.dropbox',['album'=>$album->slug,'folder_name'=>str_replace('%2F','/',rawurlencode(substr($entry->path_lower,1)))])}}">--}}
                                        {{--<i class="big folder icon"></i>--}}
                                        {{--<span>{{$entry->name}}</span>--}}
                                    {{--</a>--}}
                                {{--</li>--}}
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