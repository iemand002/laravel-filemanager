@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="content">
            <div class="form-group">
                <label for="filename" class='col-sm-2 control-label'>Filepicker</label>
                <div class="col-sm-6">
                    <div class="input-group">
                        <input type="hidden" id="fileId" name="upload_id">
                        <input type="text" name="upload_filename" id='filename' class='form-control'
                               placeholder="Choose a file">
                        <span class="input-group-btn">
                            <button class="btn btn-default" type="button"
                                    onclick="window.open('{{route('filemanager.picker')}}?id=fileId&file=filename','imagepicker', 'width=1000,height=500,scrollbars=yes,toolbar=no,location=no'); return false">
                                    Choose a file
                            </button>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection