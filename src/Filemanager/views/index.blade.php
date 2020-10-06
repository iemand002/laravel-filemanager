@extends(config('filemanager.extend_layout.normal'))
@section('pagetitle')
    {{trans('filemanager::filemanager.file_manager')}}
@endsection
@section(config('filemanager.pagetitle_section'))
    @if(config('filemanager.jquery_datatables.use')&&config('filemanager.jquery_datatables.cdn'))
        <link href="https://cdn.datatables.net/1.10.11/css/dataTables.bootstrap.min.css " type="text/css"
              rel="stylesheet">
    @endif
@endsection
@section(config('filemanager.content_section'))
    @if(config('filemanager.include_container')!='none')
        <div class="{{(config('filemanager.include_container')=='fluid')?'container-fluid':'container'}}">
            @endif
            {{-- Top Bar --}}
            <div class="row page-title-row">
                <div class="col-xs-6">
                    <h3 class="pull-left">{{trans('filemanager::filemanager.uploads')}}  </h3>
                    <div class="pull-left">
                        <ul class="breadcrumb">
                            @foreach ($breadcrumbs as $path => $disp)
                                <li><a href="{{route('filemanager.index')}}?folder={{ $path }}">{{ $disp }}</a></li>
                            @endforeach
                            <li class="active">{{ $folderName }}</li>
                        </ul>
                    </div>
                </div>
                <div class="col-xs-6 text-right">
                    <button type="button" class="btn btn-success btn-md"
                            data-toggle="modal" data-target="#modal-folder-create">
                        <i class="fa fa-plus-circle"></i> {{trans('filemanager::filemanager.new_folder')}}
                    </button>
                    <button type="button" class="btn btn-primary btn-md"
                            data-toggle="modal" data-target="#modal-file-upload">
                        <i class="fa fa-upload"></i> {{trans('filemanager::filemanager.upload')}}
                    </button>
                </div>
            </div>

            <div class="row">
                <div class="col-sm-12">

                    @if(config('filemanager.alert_messages.normal'))
                        @if (Session::has('success'))
                            <div class="alert alert-success">
                                <button type="button" class="close" data-dismiss="alert">×</button>
                                <strong>
                                    <i class="fa fa-check-circle fa-lg"></i> {{trans('filemanager::filemanager.success')}}
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

                            {{-- The Subfolders --}}
                            @foreach ($subfolders as $path => $name)
                                <tr>
                                    <td>
                                        <a href="{{route('filemanager.index')}}?folder={{ $path }}">
                                            <i class="fa fa-folder fa-lg"></i>
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
                                        <a href="{{ $file['webPath'] }}">
                                            @if (is_image($file['mimeType']))
                                                <i class="fa fa-file-image-o fa-lg"></i>
                                            @else
                                                <i class="fa fa-file-o fa-lg"></i>
                                            @endif
                                            {{ $file['name'] }}
                                        </a>
                                    </td>
                                    <td>{{ $file['mimeType'] ?? 'Unknown' }}</td>
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
            @if(config('filemanager.include_container')!='none')
        </div>
    @endif

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
                @if(Config::get('app.locale')=='nl')
                "language": {
                    "url": "//cdn.datatables.net/plug-ins/9dcbecd42ad/i18n/Dutch.json"
                }
                @endif
            });
        });
        @endif
    </script>
@stop