{{-- Create Folder Modal --}}
<div class="modal fade" id="modal-folder-create" tabindex="-1" role="dialog" aria-labelledby="modalFolderCreate">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="{{ route('filemanager.create-folder') }}"
                  class="form-horizontal">
                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                <input type="hidden" name="path" value="{{ $folder }}">
                <div class="modal-header">
                    <h5 class="modal-title">{{ trans('filemanager::filemanager.create') }} {{ trans('filemanager::filemanager.new_folder') }}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="{{ trans('filemanager::filemanager.close') }}">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group row">
                        <label for="new_folder_name" class="col-sm-3 col-form-label">
                            {{ trans('filemanager::filemanager.folder') }} {{ trans('filemanager::filemanager.name') }}
                        </label>
                        <div class="col-sm-8">
                            <input type="text" id="new_folder_name" name="new_folder"
                                   class="form-control">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-dismiss="modal" aria-label="{{ trans('filemanager::filemanager.cancel') }}">
                        {{ trans('filemanager::filemanager.cancel') }}
                    </button>
                    <button type="submit" class="btn btn-primary">
                        {{ trans('filemanager::filemanager.create') }} {{trans('filemanager::filemanager.folder') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Delete File Modal --}}
<div class="modal fade" id="modal-file-delete">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ trans('filemanager::filemanager.please_confirm') }}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="{{ trans('filemanager::filemanager.close') }}">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p class="lead">
                    <i class="fa fa-question-circle fa-lg"></i>
                    {{ trans('filemanager::filemanager.are_you_sure_del') }}
                    <kbd><span id="delete-file-name1">{{ strtolower(trans('filemanager::filemanager.file')) }}</span></kbd>
                    {{ strtolower(trans('filemanager::filemanager.file')) }}?
                </p>
            </div>
            <div class="modal-footer">
                <form method="POST" action="{{ route('filemanager.delete-file') }}">
                    <input type="hidden" name="_token" value="{{ csrf_token() }}">
                    <input type="hidden" name="_method" value="DELETE">
                    <input type="hidden" name="folder" value="{{ $folder }}">
                    <input type="hidden" name="del_file" id="delete-file-name2">
                    <button type="button" class="btn btn-light" data-dismiss="modal" aria-label="{{ trans('filemanager::filemanager.cancel') }}">
                        {{ trans('filemanager::filemanager.cancel') }}
                    </button>
                    <button type="submit" class="btn btn-danger">
                        {{ trans('filemanager::filemanager.delete') }} {{trans('filemanager::filemanager.file') }}
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- Delete Folder Modal --}}
<div class="modal fade" id="modal-folder-delete">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ trans('filemanager::filemanager.please_confirm') }}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="{{ trans('filemanager::filemanager.close') }}">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p class="lead">
                    <i class="fa fa-question-circle fa-lg"></i>
                    {{ trans('filemanager::filemanager.are_you_sure_del') }}
                    <kbd><span id="delete-folder-name1">{{ Str::headline($folderName) }}</span></kbd>
                    {{ strtolower(trans('filemanager::filemanager.folder')) }}?
                </p>
            </div>
            <div class="modal-footer">
                <form method="POST" action="{{ route('filemanager.delete-folder') }}">
                    <input type="hidden" name="_token" value="{{ csrf_token() }}">
                    <input type="hidden" name="_method" value="DELETE">
                    <input type="hidden" name="path" value="{{ $folder }}">
                    <button type="button" class="btn btn-light" data-dismiss="modal" aria-label="{{ trans('filemanager::filemanager.cancel') }}">
                        {{ trans('filemanager::filemanager.cancel') }}
                    </button>
                    <button type="submit" class="btn btn-danger">
                        {{ trans('filemanager::filemanager.delete') }} {{ trans('filemanager::filemanager.folder') }}
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- Upload File Modal --}}
<div class="modal fade" id="modal-file-upload">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="{{ route('filemanager.upload-file') }}"
                  class="form-horizontal" enctype="multipart/form-data">
                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                <input type="hidden" name="folder" value="{{ $folder }}">
                <div class="modal-header">
                    <h5 class="modal-title">{{ trans('filemanager::filemanager.upload') }} {{ trans('filemanager::filemanager.new_file') }}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="{{ trans('filemanager::filemanager.close') }}">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group row">
                        <label for="file" class="col-sm-3 col-form-label">
                            {{ trans('filemanager::filemanager.file') }}
                        </label>
                        <div class="col-sm-9">
                            <div class="custom-file">
                                <input type="file" class="custom-file-input" id="file" name="file">
                                <label class="custom-file-label" for="customFile">{{ trans('filemanager::filemanager.choose_file') }}</label>
                            </div>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label for="file_name" class="col-sm-3 col-form-label">
                            {{ trans('filemanager::filemanager.optional_filename') }}
                        </label>
                        <div class="col-sm-9">
                            <input type="text" id="file_name" name="file_name"
                                   class="form-control">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-dismiss="modal" aria-label="{{ trans('filemanager::filemanager.cancel') }}">
                        {{ trans('filemanager::filemanager.cancel') }}
                    </button>
                    <button type="submit" class="btn btn-primary">
                        {{ trans('filemanager::filemanager.upload') }} {{trans('filemanager::filemanager.file') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push(config('filemanager.javascript_section'))
    <script src="https://cdn.jsdelivr.net/npm/bs-custom-file-input/dist/bs-custom-file-input.min.js"></script>
    <script>
        $(document).ready(function () {
            bsCustomFileInput.init()
        })
    </script>
@endpush