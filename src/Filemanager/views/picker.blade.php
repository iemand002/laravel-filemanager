@extends(config('filemanager.extend_layout.picker'))

@section('pagetitle')
    {{ trans('filemanager::filemanager.file_manager') }}
@endsection

@push(config('filemanager.css_section'))
    @if(config('filemanager.jquery_datatables.use') && config('filemanager.jquery_datatables.cdn'))
        <link href="https://cdn.datatables.net/1.10.21/css/dataTables.bootstrap4.min.css" type="text/css"
              rel="stylesheet">
    @endif
    <link href="{{ asset('vendor/iemand002/filemanager/css/filemanager.css') }}" rel="stylesheet" type="text/css">
@endpush

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
                <h3 class="pull-left">{{ trans('filemanager::filemanager.uploads') }} </h3>
                <nav class="pull-left" aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        @foreach ($breadcrumbs as $path => $disp)
                            @php
                                $link = route('filemanager.picker') . "?folder=" . $path . $urlParams;
                            @endphp
                            <li class="breadcrumb-item">
                                <a href="{{ $link }}">
                                    @if($disp == 'root')
                                        <i class="fas fa-home"></i>
                                    @else
                                        {{ Str::headline($disp) }}
                                    @endif
                                </a>
                            </li>
                        @endforeach
                        <li class="breadcrumb-item active" aria-current="page">
                            @if($folderName == 'root')
                                <i class="fas fa-home"></i>
                            @else
                                {{ Str::headline($folderName) }}
                            @endif
                        </li>
                    </ol>
                </nav>
            </div>
            <div class="col-md-6 text-right">
                @if(isset($_GET['multi']))
                    <button type="button" class="btn btn-info btn-md" disabled="disabled" id="multi-add">
                        <i class="fa fa-check-square"></i> {{ trans('filemanager::filemanager.select') }}
                    </button>
                @endif
                <button type="button" class="btn btn-primary btn-md"
                        data-toggle="modal" data-target="#modal-file-upload">
                    <i class="fa fa-upload"></i> {{ trans('filemanager::filemanager.upload') }}
                </button>
                @if(is_dropbox_configured() && !is_dropbox_loggedIn())
                    <a href="{{ route('social.redirect', ['provider'=>'dropbox']) }}" class="btn btn-dropbox">
                        <i class="fab fa-dropbox"></i>
                        {{ trans('filemanager::filemanager.connect_dropbox_btn') }}
                    </a>
                @endif
                @if(is_onedrive_configured() && !is_onedrive_loggedIn())
                    <a href="{{ route('social.redirect', ['provider'=>'graph']) }}" class="btn btn-onedrive">
                        <i class="fab fa-windows"></i>
                        {{ trans('filemanager::filemanager.connect_onedrive_btn') }}
                    </a>
                @endif
            </div>
        </div>

        <div class="row">
            <div class="col-sm-12 col-md-3">
                <button class="btn btn-primary d-md-none" type="button" data-toggle="collapse"
                        data-target="#collapseFolders" aria-expanded="false" aria-controls="collapseFolders">
                    {{ trans('filemanager::filemanager.folders') }}
                </button>
                <div class="collapse" id="collapseFolders">
                    <ul class="list-unstyled">
                        @if($folderName == 'root')
                            @if(is_dropbox_loggedIn())
                                <li>
                                    @php
                                        $link = route('filemanager.pickerCloud',["dropbox",""]) . "?folder=" . $urlParams . "&cloud=dropbox";
                                    @endphp
                                    <a href="{{ $link }}" class="folder folder-dropbox">
                                        Dropbox ({{ trans('filemanager::filemanager.cloud') }})
                                    </a>
                                </li>
                            @endif

                            @if(is_onedrive_loggedIn())
                                <li>
                                    @php
                                        $link = route('filemanager.pickerCloud',["onedrive",""]) . "?folder=" . $urlParams . "&cloud=onedrive";
                                    @endphp
                                    <a href="{{ $link }}" class="folder folder-windows">
                                        OneDrive ({{ trans('filemanager::filemanager.cloud') }})
                                    </a>
                                </li>
                            @endif
                        @endif
                        {{-- The Subfolders --}}
                        @forelse ($subFolders as $path => $name)
                            <li>
                                @php
                                    $link = route('filemanager.picker') . "?folder=" . $path . $urlParams;
                                @endphp
                                <a href="{{ $link }}" class="folder">
                                    {{ Str::headline($name) }}
                                </a>
                            </li>
                        @empty
                            <li>{{ trans('filemanager::filemanager.no_folders') }}</li>
                        @endforelse
                    </ul>
                    <button type="button" class="btn btn-success btn-sm"
                            data-toggle="modal" data-target="#modal-folder-create">
                        <i class="fa fa-plus-circle"></i> {{ trans('filemanager::filemanager.new_folder') }}
                    </button>
                    <button type="button" class="btn btn-sm btn-danger"
                            data-toggle="modal" data-target="#modal-folder-delete"
                            data-folder="{{ $folderName }}">
                        <i class="fa fa-times-circle"></i>
                        {{ trans('filemanager::filemanager.delete') }}
                    </button>
                </div>
            </div>
            <div class="col-sm-12 col-md-9">

                @if(config('filemanager.alert_messages.picker'))
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
                            @if(isset($_GET['multi']))
                                <th data-sortable="false" class="checkbox-label">
                                    <label for="check-all">
                                        <input type="checkbox" id="check-all"><span
                                                class="sr-only">{{ trans('filemanager::filemanager.check_all') }}</span>
                                    </label>
                                </th>
                            @endif
                            <th>{{ trans('filemanager::filemanager.name') }}</th>
                            <th>{{ trans('filemanager::filemanager.type') }}</th>
                            <th>{{ trans('filemanager::filemanager.date') }}</th>
                            <th>{{ trans('filemanager::filemanager.Size') }}</th>
                            <th data-sortable="false">{{ trans('filemanager::filemanager.actions') }}</th>
                        </tr>
                        </thead>
                        <tbody>
                        @if(!config('filemanager.jquery_datatables.use'))
                             The Files
                            @foreach ($files as $file)
                                <tr>
                                    @if(isset($_GET['multi']))
                                        <td class="checkbox-label">
                                            <label for="check{{ $file['id'] }}">
                                                <input type="checkbox" name="files[]" id="check{{ $file['id'] }}"
                                                       data-file-id="{{ $file['id'] }}"
                                                       data-file-name="{{ $file['name'] }}">
                                                <span class="sr-only">{{ trans('filemanager::filemanager.check') }}</span>
                                            </label>
                                        </td>
                                    @endif
                                    <td>
                                        <a class="file" href="#" data-file-id="{{ $file['id'] }}"
                                           data-file-name="{{ $file['name'] }}">
                                            @if (is_image($file['mimeType']))
                                                <i class="far fa-file-image"></i>
                                            @else
                                                <i class="far fa-file-alt"></i>
                                            @endif
                                            {{ $file['name'] }}
                                        </a>
                                    </td>
                                    <td class="mimeType">{{ $file['mimeType'] ?? 'Unknown' }}</td>
                                    <td>{{ $file['time_taken']->format('j-M-y g:ia') }}</td>
                                    <td>{{ human_filesize($file['size']) }}</td>
                                    <td>
                                        <button type="button"
                                                class="btn btn-sm btn-danger btn-delete"
                                                data-toggle="tooltip"
                                                title="{{ trans('filemanager::filemanager.delete') }}"
                                                data-name="{{ $file['name'] }}"
                                        >
                                            <i class="fa fa-times-circle"></i>
                                        </button>
                                        @if (is_image($file['mimeType']))
                                            <button type="button"
                                                    class="btn btn-sm btn-success btn-image"
                                                    data-toggle="tooltip"
                                                    title="{{ trans('filemanager::filemanager.preview') }}"
                                                    data-path="{{ $file['webPath'] }}"
                                            >
                                                <i class="fa fa-eye"></i>
                                            </button>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    @include('iemand002/filemanager::_modals')
    @include('iemand002/filemanager::_modalView')

@stop

@push(config('filemanager.javascript_section'))
    @include('iemand002/filemanager::_pickerJs')
@endpush