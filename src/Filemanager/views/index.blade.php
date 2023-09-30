@extends(config('filemanager.extend_layout.normal'))

@section('pagetitle')
    {{trans('filemanager::filemanager.file_manager')}}
@endsection

@push(config('filemanager.css_section'))
    @if(config('filemanager.jquery_datatables.use') && config('filemanager.jquery_datatables.cdn'))
        <link href="https://cdn.datatables.net/1.10.21/css/dataTables.bootstrap4.min.css" type="text/css"
              rel="stylesheet">
    @endif
    <link href="{{ asset('vendor/iemand002/filemanager/css/filemanager.css') }}" rel="stylesheet" type="text/css">
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
                                    $link = route('filemanager.index') . "?folder=" . $path;
                                @endphp
                                <li class="breadcrumb-item"><a href="{{ $link }}">
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
                                            $link = route('filemanager.pickerCloud',["dropbox",""]) . "&cloud=dropbox";
                                        @endphp
                                        <a href="{{ $link }}" class="folder folder-dropbox">
                                            Dropbox ({{ trans('filemanager::filemanager.cloud') }})
                                        </a>
                                    </li>
                                @endif

                                @if(is_onedrive_loggedIn())
                                    <li>
                                        @php
                                            $link = route('filemanager.pickerCloud',["onedrive",""]) . "&cloud=onedrive";
                                        @endphp
                                        <a href="{{ $link }}" class="folder folder-windows">
                                            OneDrive ({{ trans('filemanager::filemanager.cloud') }})
                                        </a>
                                    </li>
                                @endif
                            @endif
                            {{-- The Subfolders --}}
                            @forelse ($subfolders as $path => $name)
                                <li>
                                    @php
                                        $link = route('filemanager.index') . "?folder=" . $path;
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
                        >
                            <i class="fa fa-times-circle"></i>
                            {{ trans('filemanager::filemanager.delete_folder') }}
                        </button>
                    </div>
                </div>
                <div class="col-sm-12 col-md-9">
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

                            {{-- The Files --}}
{{--                            @foreach ($files as $file)--}}
{{--                                <tr>--}}
{{--                                    <td>--}}
{{--                                        <a href="{{ $file['webPath'] }}">--}}
{{--                                            @if (is_image($file['mimeType']))--}}
{{--                                                <i class="far fa-file-image"></i>--}}
{{--                                            @else--}}
{{--                                                <i class="far fa-file-alt"></i>--}}
{{--                                            @endif--}}
{{--                                            {{ $file['name'] }}--}}
{{--                                        </a>--}}
{{--                                    </td>--}}
{{--                                    <td class="mimeType">{{ $file['mimeType'] ?? 'Unknown' }}</td>--}}
{{--                                    <td>{{ $file['time_taken']->format('j-M-y g:ia') }}</td>--}}
{{--                                    <td>{{ human_filesize($file['size']) }}</td>--}}
{{--                                    <td>--}}
{{--                                        <button type="button"--}}
{{--                                                class="btn btn-sm btn-danger btn-delete"--}}
{{--                                                data-toggle="tooltip"--}}
{{--                                                title="{{ trans('filemanager::filemanager.delete') }}"--}}
{{--                                                data-name="{{ $file['name'] }}"--}}
{{--                                        >--}}
{{--                                            <i class="fa fa-times-circle"></i>--}}
{{--                                        </button>--}}
{{--                                        @if (is_image($file['mimeType']))--}}
{{--                                            <button type="button"--}}
{{--                                                    class="btn btn-sm btn-success btn-image"--}}
{{--                                                    data-toggle="tooltip"--}}
{{--                                                    title="{{ trans('filemanager::filemanager.preview') }}"--}}
{{--                                                    data-path="{{ $file['webPath'] }}"--}}
{{--                                            >--}}
{{--                                                <i class="fa fa-eye"></i>--}}
{{--                                            </button>--}}
{{--                                        @endif--}}
{{--                                    </td>--}}
{{--                                </tr>--}}
{{--                            @endforeach--}}

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
    @include('iemand002/filemanager::_pickerJs')
@endpush