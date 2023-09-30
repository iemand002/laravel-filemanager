@extends(config('filemanager.extend_layout.picker'))

@section('pagetitle')
    {{trans('filemanager::filemanager.file_manager_onedrive')}}
@endsection

@push(config('filemanager.css_section'))
    @if(config('filemanager.jquery_datatables.use')&&config('filemanager.jquery_datatables.cdn'))
        <link href="https://cdn.datatables.net/1.10.21/css/dataTables.bootstrap4.min.css" type="text/css"
              rel="stylesheet">
    @endif
    <link href="{{ asset('vendor/iemand002/filemanager/css/filemanager.css') }}" rel="stylesheet" type="text/css">
@endpush

@section(config('filemanager.content_section'))
    @php
        $urlParams = '';
        if (isset($_GET['CKEditor']))
            $urlParams .= "&CKEditor=" . $_GET['CKEditor']."&CKEditorFuncNum=" . $_GET['CKEditorFuncNum'];
        if (isset($_GET['id']))
            $urlParams .= "&id=" . $_GET['id'];
        if (isset($_GET['file']))
            $urlParams .= "&file=" . $_GET['file'];
        if (isset($_GET['multi']))
            $urlParams .= "&multi=true";
    @endphp
    <div class="container-fluid">

        {{-- Top Bar --}}
        <div class="row page-title-row">
            <div class="col-md-6">
                <h3 class="pull-left">{{ trans('filemanager::filemanager.file_manager_onedrive') }} </h3>
                <nav class="pull-left">
                    <ol class="breadcrumb">
                        @php
                            $link = route('filemanager.picker') . "?folders=" . $urlParams;
                        @endphp
                        <li class="breadcrumb-item"><a href="{{ $link }}"><i class="fas fa-home"></i></a></li>
                        @php
                            $link = route('filemanager.pickerCloud','onedrive') . "?folder=&cloud=onedrive". $urlParams;
                            $parent = explode('/',$data->value[0]->parentReference->path);
                        @endphp
                        <li class="breadcrumb-item"><a href="{{ $link }}"><i class="fab fa-windows"></i> OneDrive</a></li>
                        @php $foldersUrl = $foldersByName = '' @endphp
                        @foreach($folders as $folder)
                            @if($folder != '')
                                @php
                                    $foldersUrl = ($foldersUrl!=''?$foldersUrl.'-':'') . $folder;
                                    $foldersByName = urldecode($parent[3 + $loop->index]);
                                @endphp
                                @if(end($folders)==$folder)
                                    <li class="breadcrumb-item active">{{ urldecode($parent[3 + $loop->index]) }}</li>
                                @else
                                    <li class="breadcrumb-item">
                                        <a href="{{ route('filemanager.pickerCloud','onedrive') . "?folder=" . $foldersByName . "&folders=" . $foldersUrl . '&cloud=onedrive' . $urlParams }}">
                                            {{ urldecode($parent[3+$loop->index]) }}
                                        </a>
                                    </li>
                                @endif
                            @endif
                        @endforeach
                    </ol>
                </nav>
            </div>
            <div class="col-md-6 text-right">
                @if(isset($_GET['multi']))
                    <button type="button" class="btn btn-info btn-md" disabled="disabled" id="multi-add">
                        <i class="fa fa-check-square"></i> {{ trans('filemanager::filemanager.select') }}
                    </button>
                @endif
            </div>
        </div>

        <div class="row">
            <div class="col-sm-12 col-md-3">
                <button class="btn btn-primary d-md-none" type="button" data-toggle="collapse" data-target="#collapseFolders" aria-expanded="false" aria-controls="collapseFolders">
                    {{ trans('filemanager::filemanager.folders') }}
                </button>
                <div class="collapse" id="collapseFolders">
                    <ul class="list-unstyled">
                        {{-- The Subfolders --}}
                        @php
                            $noFolders = true;
                        @endphp
                        @foreach($data->value as $entry)
                            @if(!property_exists($entry, 'file'))
                            <li>
                                @php
                                    $link = route('filemanager.pickerCloud', ['onedrive']) . "?folder=" . ($foldersByName == '' ? $entry->name : $foldersByName . "/" . $entry->name) . "&folders=" . ($foldersUrl != '' ? $foldersUrl . '-' : '') . $entry->id . '&cloud=onedrive'. $urlParams;
                                    $noFolders = false;
                                @endphp
                                <a href="{{$link}}">
                                    {{ $entry->name }}
                                </a>
                            </li>
                            @endif
                        @endforeach
                        @if($noFolders)
                            <li>{{ trans('filemanager::filemanager.no_folders') }}</li>
                        @endif
                    </ul>
                </div>
            </div>
                <div class="col-sm-12 col-md-9">
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
                            <th>{{ trans('filemanager::filemanager.name') }}</th>
                            <th>{{ trans('filemanager::filemanager.type') }}</th>
                            <th>{{ trans('filemanager::filemanager.date') }}</th>
                            <th>{{ trans('filemanager::filemanager.Size') }}</th>
                            <th data-sortable="false">{{ trans('filemanager::filemanager.actions') }}</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($data->value as $entry)
                            @if(property_exists($entry, 'file'))
                                @php
                                    $mimeType = $entry->file->mimeType
                                @endphp
                                <tr>
                                    @if(isset($_GET['multi']))
                                        <td class="checkbox-label">
                                            <label for="check{{ $loop->index }}">
                                                <input type="checkbox" name="files[]" id="check{{ $loop->index }}"
                                                       data-file-id="{{$entry->id}}" data-file-name="{{ $entry->name }}"
                                                       data-file-date="{{ \Carbon\Carbon::createFromTimeString($entry->fileSystemInfo->createdDateTime)->format('Y-m-d H:i:s') }}"
                                                       data-file-dimension="@if (property_exists($entry, 'image')){{ $entry->image->width }}x{{ $entry->image->height }}@endif"
                                                       data-file-mime-type="{{ $mimeType }}"
                                                >
                                                <span class="sr-only">{{ trans('filemanager::filemanager.check') }}</span>
                                            </label>
                                        </td>
                                    @endif
                                    <td>
                                        <a class="file" href="#" data-file-id="{{$entry->id}}"
                                           data-file-name="{{ $entry->name }}"
                                           data-file-date="{{ \Carbon\Carbon::createFromTimeString($entry->fileSystemInfo->createdDateTime)->format('Y-m-d H:i:s') }}"
                                           data-file-dimension="@if (property_exists($entry, 'image')){{ $entry->image->width }}x{{ $entry->image->height }}@endif"
                                           data-file-mime-type="{{ $mimeType }}"
                                        >
                                            @if (property_exists($entry, 'image'))
                                                <i class="far fa-file-image"></i>
                                            @else
                                                <i class="far fa-file-alt"></i>
                                            @endif
                                            {{ $entry->name }}
                                        </a>
                                    </td>
                                    <td>{{ $mimeType }}</td>
                                    <td>{{ \Carbon\Carbon::createFromTimeString($entry->fileSystemInfo->createdDateTime)->format('j-M-y g:ia') }}</td>
                                    <td>{{ human_filesize($entry->size) }}</td>
                                    <td>
                                        @if (property_exists($entry, 'image'))
                                            <button type="button" class="btn btn-sm btn-success"
                                                    onclick="preview_image('{{ route('filemanager.getPicture',['provider'=>'onedrive', $entry->id]) }}')">
                                                <i class="fa fa-eye fa-lg"></i>
                                                {{ trans('filemanager::filemanager.preview') }}
                                            </button>
                                        @endif
                                    </td>
                                </tr>
                            @endif
                        @endforeach


                        </tbody>
                    </table>
                </div>
                </div>
            </div>
        </div>
    </div>

    @include('iemand002/filemanager::_modalView')

@stop

@push(config('filemanager.javascript_section'))
    @include('iemand002/filemanager::_pickerJs')
@endpush