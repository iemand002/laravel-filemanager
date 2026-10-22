{{-- View Image Modal --}}
<div class="modal fade" id="modal-image-view">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ trans('filemanager::filemanager.image' )}} {{ trans('filemanager::filemanager.preview') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{trans('filemanager::filemanager.close')}}">
                </button>
            </div>
            <div class="modal-body">
                <img id="preview-image" src="" class="img-fluid" alt="">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" data-bs-dismiss="modal" aria-label="{{ trans('filemanager::filemanager.close') }}">
                    {{ trans('filemanager::filemanager.close') }}
                </button>
            </div>
        </div>
    </div>
</div>
