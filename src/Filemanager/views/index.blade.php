@extends(config('filemanager.extend_layout.normal'))

@section('pagetitle')
    {{trans('filemanager::filemanager.file_manager')}}
@endsection

@push(config('filemanager.css_section'))
    @if(config('filemanager.jquery_datatables.use') && config('filemanager.jquery_datatables.cdn'))
        <link href="https://cdn.datatables.net/1.10.21/css/dataTables.bootstrap4.min.css" type="text/css"
              rel="stylesheet">
    @endif
@endpush

@section(config('filemanager.content_section'))
    @if(config('filemanager.include_container') != 'none')
        <div class="{{ config('filemanager.include_container') == 'fluid' ? 'container-fluid' : 'container' }}">
            @endif
            {{-- Top Bar --}}
            <div class="row page-title-row">
                <div class="col-md-6">
                    <h3 class="pull-left">{{ trans('filemanager::filemanager.uploads') }}</h3>
                    <nav class="pull-left" aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            @foreach ($breadcrumbs as $path => $disp)
                                @php
                                    $link = route('filemanager.picker') . "?folder=" . $path;
                                @endphp
                                <li class="breadcrumb-item"><a href="{{ $link }}">@if($disp == 'root')<i class="fas fa-home"></i>@else{{ $disp }}@endif</a></li>
                            @endforeach
                            <li class="breadcrumb-item active" aria-current="page">@if($folderName == 'root')<i class="fas fa-home"></i>@else{{ $folderName }}@endif</li>
                        </ol>
                    </nav>
                </div>
                <div class="col-md-6 text-right">
                    <button type="button" class="btn btn-success btn-md"
                            data-toggle="modal" data-target="#modal-folder-create">
                        <i class="fa fa-plus-circle"></i> {{ trans('filemanager::filemanager.new_folder') }}
                    </button>
                    <button type="button" class="btn btn-primary btn-md"
                            data-toggle="modal" data-target="#modal-file-upload">
                        <i class="fa fa-upload"></i> {{ trans('filemanager::filemanager.upload') }}
                    </button>
                </div>
            </div>

            <div class="row">
                <div class="col-sm-12">

                    @if(config('filemanager.alert_messages.normal'))
                        @if(Session::has('success'))
                            <div class="alert alert-success">
                                <button type="button" class="close" data-dismiss="alert">×</button>
                                <strong>
                                    <i class="fa fa-check-circle"></i> {{ trans('filemanager::filemanager.success') }}
                                </strong>
                                {{ Session::get('success') }}
                            </div>
                        @endif
                        @if($errors->any())
                            <div class="alert alert-danger">
                                <button type="button" class="close" data-dismiss="alert">×</button>
                                <strong>{{ trans('filemanager::filemanager.whoops') }}</strong>
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
                                <th>{{ trans('filemanager::filemanager.name') }}</th>
                                <th>{{ trans('filemanager::filemanager.type') }}</th>
                                <th>{{ trans('filemanager::filemanager.date') }}</th>
                                <th>{{ trans('filemanager::filemanager.Size') }}</th>
                                <th data-sortable="false">{{ trans('filemanager::filemanager.actions') }}</th>
                            </tr>
                            </thead>
                            <tbody>

                            @if(is_dropbox_loggedIn())
                                <tr>
                                    <td>
                                        @php
                                            $link = route('filemanager.pickerCloud',["dropbox",""]) . "?folder=" . $urlParams . '&cloud=dropbox';
                                        @endphp
                                        <a href="{{ $link }}">
                                            <i class="fab fa-dropbox"></i>
                                            Dropbox
                                        </a>
                                    </td>
                                    <td>{{ trans('filemanager::filemanager.cloud') }}</td>
                                    <td>-</td>
                                    <td>-</td>
                                    <td>-</td>
                                </tr>
                            @endif

                            @if(is_onedrive_loggedIn())
                                <tr>
                                    <td>
                                        @php
                                            $link = route('filemanager.pickerCloud',["onedrive",""]) . "?folder=" . $urlParams . '&cloud=onedrive';
                                        @endphp
                                        <a href="{{ $link }}">
                                            <i class="fab fa-windows"></i>
                                            OneDrive
                                        </a>
                                    </td>
                                    <td>{{ trans('filemanager::filemanager.cloud') }}</td>
                                    <td>-</td>
                                    <td>-</td>
                                    <td>-</td>
                                </tr>
                            @endif

                            {{-- The Subfolders --}}
                            @foreach ($subfolders as $path => $name)
                                <tr>
                                    <td>
                                        <a href="{{ route('filemanager.index') }}?folder={{ $path }}">
                                            <i class="fa fa-folder"></i>
                                            {{ $name }}
                                        </a>
                                    </td>
                                    <td>{{ trans('filemanager::filemanager.folder') }}</td>
                                    <td>-</td>
                                    <td>-</td>
                                    <td>
                                        <button type="button" class="btn btn-sm btn-danger"
                                                onclick="delete_folder('{{ $name }}')">
                                            <i class="fa fa-times-circle"></i>
                                            {{ trans('filemanager::filemanager.delete') }}
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
                                                <i class="far fa-file-image"></i>
                                            @else
                                                <i class="far fa-file-alt"></i>
                                            @endif
                                            {{ $file['name'] }}
                                        </a>
                                    </td>
                                    <td>{{ $file['mimeType'] ?? 'Unknown' }}</td>
                                    <td>{{ $file['modified']->format('j-M-y g:ia') }}</td>
                                    <td>{{ human_filesize($file['size']) }}</td>
                                    <td>
                                        <button type="button" class="btn btn-sm btn-danger"
                                                onclick="delete_file('{{ $file['name'] }}')">
                                            <i class="fa fa-times-circle"></i>
                                            {{ trans('filemanager::filemanager.delete') }}
                                        </button>
                                        @if (is_image($file['mimeType']))
                                            <button type="button" class="btn btn-sm btn-success"
                                                    onclick="preview_image('{{ $file['webPath'] }}')">
                                                <i class="fa fa-eye"></i>
                                                {{ trans('filemanager::filemanager.preview') }}
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
    @include('iemand002/filemanager::_modalView')

@stop

@push(config('filemanager.javascript_section'))
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
@endpush