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
    @php
        $urlParams = '';
        if (isset($_GET['CKEditor']))
            $urlParams .= "&CKEditor=".$_GET['CKEditor']."&CKEditorFuncNum=".$_GET['CKEditorFuncNum'];
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
                <h3 class="pull-left">{{trans('filemanager::filemanager.file_manager_dropbox')}} </h3>
                <div class="pull-left">
                    <ul class="breadcrumb">
                        @php
                            $link = route('filemanager.picker') . "?folder=" . $urlParams;
                        @endphp
                        <li><a href="{{$link}}">root</a></li>
                        @php
                            $link = route('filemanager.pickerCloud','dropbox') . "?folder=&cloud=dropbox". $urlParams;
                        @endphp
                        <li><a href="{{$link}}"><i class="fa fa-dropbox"></i> Dropbox</a></li>
                        @for($i=0;$i<sizeof($folder);$i++)
                            @if($folder[$i]!='')
                                @php
                                    $link = route('filemanager.pickerCloud','dropbox') . "?folder=". substr($folder[$i],1) . '&cloud=dropbox' . $urlParams;
                                @endphp
                                @if($i==sizeof($folder)-1)
                                    <li class="active">{{$folder_split[$i]}}</li>
                                @else
                                    <li><a href="{{$link}}">{{$folder_split[$i]}}</a></li>
                                @endif
                            @endif
                        @endfor
                    </ul>
                </div>
            </div>
            <div class="col-md-6 text-right">
                @if(isset($_GET['multi']))
                    <button type="button" class="btn btn-info btn-md" disabled="disabled" id="multi-add">
                        <i class="fa fa-check-square"></i> {{trans('filemanager::filemanager.select')}}
                    </button>
                @endif
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
                                @php
                                    $mimeType = fileMimeType($entry->path_display)
                                @endphp
                                <tr>
                                    @if(isset($_GET['multi']))
                                        <td class="checkbox-label">
                                            <label for="check{{$loop->index}}">
                                                <input type="checkbox" name="files[]" id="check{{$loop->index}}"
                                                       data-file-id="{{$entry->id}}" data-file-name="{{ $entry->name }}"
                                                       data-file-date="{{ \Carbon\Carbon::createFromTimeString($entry->client_modified)->format('Y-m-d H:i:s') }}"
                                                       data-file-dimension="@if (is_image($mimeType)){{$entry->media_info->metadata->dimensions->width}}x{{$entry->media_info->metadata->dimensions->height}}@endif"
                                                       data-file-mime-type="{{$mimeType}}"
                                                >
                                                <span class="sr-only">{{trans('filemanager::filemanager.check')}}</span>
                                            </label>
                                        </td>
                                    @endif
                                    <td>
                                        <a class="file" href="#" data-file-id="{{$entry->id}}"
                                           data-file-name="{{ $entry->name }}"
                                           data-file-date="{{ \Carbon\Carbon::createFromTimeString($entry->client_modified)->format('Y-m-d H:i:s') }}"
                                           data-file-dimension="@if (is_image($mimeType)){{$entry->media_info->metadata->dimensions->width}}x{{$entry->media_info->metadata->dimensions->height}}@endif"
                                           data-file-mime-type="{{$mimeType}}"
                                        >
                                            @if (is_image($mimeType))
                                                <i class="fa fa-file-image-o fa-lg fa-fw"></i>
                                            @else
                                                <i class="fa fa-file-o fa-lg fa-fw"></i>
                                            @endif
                                            {{ $entry->name }}
                                        </a>
                                    </td>
                                    <td>{{ $mimeType }}</td>
                                    <td>{{ \Carbon\Carbon::createFromTimeString($entry->client_modified)->format('j-M-y g:ia') }}</td>
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
                                        @php
                                            $link = route('filemanager.pickerCloud',['dropbox']) . "?folder=" . str_replace('%2F','/',rawurlencode(substr($entry->path_lower,1))) . '&cloud=dropbox'. $urlParams;
                                        @endphp
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
    @include('iemand002/filemanager::_pickerJs')
@stop