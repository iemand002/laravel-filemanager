@extends(config('filemanager.extend_layout.picker'))

@section('pagetitle')
    {{trans('filemanager::filemanager.file_manager')}}
@endsection

@section(config('filemanager.css_section'))
    @if(config('filemanager.jquery_datatables.use') && config('filemanager.jquery_datatables.cdn'))
        <link href="https://cdn.datatables.net/1.10.11/css/dataTables.bootstrap.min.css" type="text/css"
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
    @php
        $urlParams = '';
        if (isset($_GET['CKEditor'])) {
            $urlParams .= "&CKEditor=" . $_GET['CKEditor'] . "&CKEditorFuncNum=" . $_GET['CKEditorFuncNum'];
        }
        if (isset($_GET['id'])) {
            $urlParams .= "&id=" . $_GET['id'];
        }
        if (isset($_GET['file'])) {
            $urlParams .= "&file=" . $_GET['file'];
        }
        if (isset($_GET['multi'])) {
            $urlParams .= "&multi=true";
        }
    @endphp
    <div class="container-fluid">

        {{-- Top Bar --}}
        <div class="row page-title-row">
            <div class="col-md-6">
                <h3 class="pull-left">{{trans('filemanager::filemanager.uploads')}} </h3>
                <div class="pull-left">
                    <ul class="breadcrumb">
                        @foreach ($breadcrumbs as $path => $disp)
                            @php
                                $link = route('filemanager.picker') . "?folder=" . $path . '&cloud=dropbox' . $urlParams;
                            @endphp
                            <li><a href="{{$link}}">{{ $disp }}</a></li>
                        @endforeach
                        <li class="active">{{ $folderName }}</li>
                    </ul>
                </div>
            </div>
            <div class="col-md-6 text-right">
                @if(isset($_GET['multi']))
                    <button type="button" class="btn btn-info btn-md" disabled="disabled" id="multi-add">
                        <i class="fa fa-check-square"></i> {{trans('filemanager::filemanager.select')}}
                    </button>
                @endif
                <button type="button" class="btn btn-success btn-md"
                        data-toggle="modal" data-target="#modal-folder-create">
                    <i class="fa fa-plus-circle"></i> {{trans('filemanager::filemanager.new_folder')}}
                </button>
                <button type="button" class="btn btn-primary btn-md"
                        data-toggle="modal" data-target="#modal-file-upload">
                    <i class="fa fa-upload"></i> {{trans('filemanager::filemanager.upload')}}
                </button>
                @if(is_dropbox_configured() && !is_dropbox_loggedIn())
                    <a href="{{ route('social.redirect', ['provider'=>'dropbox']) }}" class="btn btn-dropbox">
                        <i class="fa fa-dropbox"></i>
                        {{trans('filemanager::filemanager.connect_dropbox_btn')}}
                    </a>
                @endif
                @if(is_onedrive_configured() && !is_onedrive_loggedIn())
                    <a href="{{ route('social.redirect', ['provider'=>'graph']) }}" class="btn btn-onedrive">
                        <i class="fa fa-windows"></i>
                        {{trans('filemanager::filemanager.connect_onedrive_btn')}}
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

                        @if(is_dropbox_loggedIn())
                            <tr>
                                @if(isset($_GET['multi']))
                                    <td>&nbsp;</td>
                                @endif
                                <td>
                                    @php
                                        $link = route('filemanager.pickerCloud',["dropbox",""]) . "?folder=" . $urlParams . '&cloud=dropbox';
                                    @endphp
                                    <a href="{{$link}}">
                                        <i class="fa fa-dropbox fa-lg fa-fw"></i>
                                        Dropbox
                                    </a>
                                </td>
                                <td>{{trans('filemanager::filemanager.cloud')}}</td>
                                <td>-</td>
                                <td>-</td>
                                <td>-</td>
                            </tr>
                        @endif

                        @if(is_onedrive_loggedIn())
                            <tr>
                                @if(isset($_GET['multi']))
                                    <td>&nbsp;</td>
                                @endif
                                <td>
                                    @php
                                        $link = route('filemanager.pickerCloud',["onedrive",""]) . "?folder=" . $urlParams . '&cloud=onedrive';
                                    @endphp
                                    <a href="{{$link}}">
                                        <i class="fa fa-windows fa-lg fa-fw"></i>
                                        OneDrive
                                    </a>
                                </td>
                                <td>{{trans('filemanager::filemanager.cloud')}}</td>
                                <td>-</td>
                                <td>-</td>
                                <td>-</td>
                            </tr>
                        @endif
                        {{-- The Subfolders --}}
                        @foreach ($subfolders as $path => $name)
                            <tr>
                                @if(isset($_GET['multi']))
                                    <td>&nbsp;</td>
                                @endif
                                <td>
                                    @php
                                        $link = route('filemanager.picker') . "?folder=" . $path . $urlParams;
                                    @endphp
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
                                @if(isset($_GET['multi']))
                                    <td class="checkbox-label">
                                        <label for="check{{$file['id']}}">
                                            <input type="checkbox" name="files[]" id="check{{$file['id']}}"
                                                   data-file-id="{{$file['id']}}" data-file-name="{{$file['name']}}">
                                            <span class="sr-only">{{trans('filemanager::filemanager.check')}}</span>
                                        </label>
                                    </td>
                                @endif
                                <td>
                                    <a class="file" href="#" data-file-id="{{$file['id']}}"
                                       data-file-name="{{$file['name']}}">
                                        @if (is_image($file['mimeType']))
                                            <i class="fa fa-file-image-o fa-lg fa-fw"></i>
                                        @else
                                            <i class="fa fa-file-o fa-lg fa-fw"></i>
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
    </div>

    @include('iemand002/filemanager::_modals')
    @include('iemand002/filemanager::_modalView')

@stop

@section(config('filemanager.javascript_section'))
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

    </script>
    @include('iemand002/filemanager::_pickerJs')
@stop