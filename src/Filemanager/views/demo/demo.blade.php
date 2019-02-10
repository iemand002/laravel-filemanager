@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="content">
            <div class="row">
                <h3>Single</h3>
                <div class="form-group">
                    <label for="filename" class='col-sm-2 control-label'>Filepicker</label>
                    <div class="col-sm-6">
                        <div class="input-group">
                            <input type="hidden" id="upload_id" name="upload_id">
                            <input type="text" name="upload_filename" id='filename' class='form-control'
                                   placeholder="{{trans('filemanager::filemanager.choose_file')}}">
                            <span class="input-group-btn">
                            <button class="btn btn-default" type="button"
                                    onclick="window.open('{{route('filemanager.picker')}}?id=upload_id&file=filename','imagepicker', 'width=1000,height=500,scrollbars=yes,toolbar=no,location=no'); return false">
                                    {{trans('filemanager::filemanager.choose_file')}}
                            </button>
                        </span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <h3>Multi</h3>
                <div id="pictures" class="row">
                    <?php $i = 1;?>

                    <input type="hidden" name="picturecount" value="{{$i-1}}" id="picturecount">
                    <div class="col-md-4" id="pic{{$i}}">
                        <div class="form-group">
                            <img alt="dummy placeholder" src="/img/dummy.png" class="img img-responsive"
                                 id="image-preview{{$i}}">
                            <div class="input-group">
                                <input type="hidden" name="picture{{$i}}" id="picture{{$i}}">
                                <input type="hidden" id="upload_id{{$i}}" name="upload_id{{$i}}" data-count="{{$i}}">
                                <button id="picker{{$i}}" type="button" class="btn btn-success"
                                        onclick="window.open('{{route('filemanager.picker')}}?id=upload_id{{$i}}&file=picture{{$i}}&add=true&multi=true','imagepicker', 'width=1000,height=500,scrollbars=yes,toolbar=no,location=no'); return false">
                                    <i class="fa fa-plus-circle"></i> {{trans('filemanager::filemanager.choose_picture')}}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('js')

    <script>
        // Listen to localstorage change
        $(window).on('storage', message_receive);

        function message_receive(ev)
        {
            if (ev.originalEvent.key !== 'fm_data') return; // ignore other keys
            var data = JSON.parse(ev.originalEvent.newValue);
            if (!data) return; // ignore empty msg or msg reset

            var folder = data.folder;
            var file = data.files[0];
            var count = parseInt(document.getElementById('picturecount').value);

            if (data.type === 'single') {
                // add or update a single image
                var upload_id = document.getElementById(data.id);
                upload_id.value = file.fileId;
                document.getElementById(data.file).value = folder + file.fileName;
                handle_image_change(parseInt(upload_id.dataset.count));
                if (count + 1 === parseInt(upload_id.dataset.count)){
                    // add a new dummy if needed
                    count++;
                    add_dummy(count+1);
                }
            } else {
                for (var i = 0; i < data.files.length; i++) {
                    // add all the images to the page
                    file = data.files[i];
                    count++;
                    document.getElementById('upload_id' + count).value = file.fileId;
                    document.getElementById('picture' + count).value = folder + file.fileName;
                    handle_image_change(count);
                    add_dummy(count + 1);
                }
            }
            document.getElementById('picturecount').value = count

        }

        function handle_image_change(count) {
            // preview the image
            $("#image-preview" + count).attr("src", function () {

                var value = $("#picture" + count).val();
                if (value.substr(0, 4) !== 'http') {
                    value = '/uploads' + value;
                }
                $("#picker" + count).attr("onclick", function () {
                    return "window.open('{{route('filemanager.picker')}}?id=upload_id" + count + "&file=picture" + count + "','imagepicker', 'width=1000,height=500,scrollbars=yes,toolbar=no,location=no'); return false";
                }).html('<i class="fa fa-pencil-square"></i> {{trans("filemanager::filemanager.change_picture")}}');
                return value;
            });
        }

        function add_dummy(count) {
            // add the new dummy
            $("#pictures").append('<div class="col-md-4" id="pic' + count + '">' +
                '<div class="form-group">' +
                '<img alt="dummy placeholder" src="/img/dummy.png" class="img img-responsive" id="image-preview' + count + '">' +
                '<div class="input-group">' +
                '<input id="picture' + count + '" name="picture' + count + '" type="hidden">' +
                '<input id="upload_id' + count + '" name="upload_id' + count + '" data-count=' + count + '" type="hidden">' +
                '<button id="picker' + count + '" type="button" class="btn btn-success"' +
                'onclick="window.open(\'{{route('filemanager.picker')}}?id=upload_id' + count + '&file=picture' + count + '&count=' + count + '&add=true&multi=true\',\'imagepicker\', \'width=1000,height=500,scrollbars=yes,toolbar=no,location=no\'); return false">' +
                '<i class="fa fa-plus-circle"></i> {{trans('filemanager::filemanager.choose_picture')}}' +
                '</button> ' +
                '</div>' +
                '</div>' +
                '</div>');
            $("#picturecount").val(count - 1);
        }
    </script>
@endsection