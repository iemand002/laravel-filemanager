@extends('layouts.app')

@section('content')
    <div class="container">
        <h3>Single</h3>
        <div class="form-group row">
            <label for="filename" class='col-sm-2 control-label'>Filepicker</label>
            <div class="col-sm-6">
                <div class="input-group">
                    <input type="hidden" id="upload_id" name="upload_id">
                    <input type="text" name="upload_filename" id='filename' class='form-control'
                           placeholder="{{trans('filemanager::filemanager.choose_file')}}">
                    <div class="input-group-append">
                        <button class="btn btn-primary" type="button"
                                onclick="window.open('{{route('filemanager.picker')}}?id=upload_id&file=filename','imagepicker', 'width=1000,height=500,scrollbars=yes,toolbar=no,location=no'); return false">
                            {{trans('filemanager::filemanager.choose_file')}}
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <h3>Multi</h3>
        <div id="pictures" class="row">
            @php
              $i = 1;
            @endphp

            <input type="hidden" name="picturecount" value="{{$i-1}}" id="picturecount">
            <div class="col-md-4" id="pic{{$i}}">
                <div class="form-group">
                    <img alt="dummy placeholder" src="/img/dummy.png" class="img img-fluid"
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

        <h3>CKEditor</h3>
        <div class="row">
            <label for="body" class="col-12">Text</label>
            <div class="col-12">
                <textarea class="form-control" id="body"></textarea>
            </div>


        </div>
    </div>
@endsection

@push(config('filemanager.css_section'))
    <link rel="stylesheet" href="https://cdn.ckeditor.com/ckeditor5/44.3.0/ckeditor5.css" crossorigin>
@endpush
@push(config('filemanager.javascript_section'))
    <script src="https://cdn.ckeditor.com/ckeditor5/48.3.0/ckeditor5.umd.js" crossorigin></script>
    <script>
      // 1. Functie om uw eigen file picker te openen en afbeelding in te voegen
      function openMijnCustomFilePicker(editor) {
        // Vervang dit prompt-voorbeeld door uw eigen modal of window.open()

        window.ckeditorCallback = function(fileUrl) {
          editor.model.change(writer => {
            const imageElement = writer.createElement('imageBlock', { src: fileUrl });
            editor.model.insertContent(imageElement, editor.model.document.selection);
          });
        };
        window.open('{{route('filemanager.picker')}}?CKEditor=true','imagepicker', 'width=1000,height=500,scrollbars=yes,toolbar=no,location=no');
      }

      /**
       * This configuration was generated using the CKEditor 5 Builder. You can modify it anytime using this link:
       * https://ckeditor.com/ckeditor-5/builder/?redirect=portal#installation/NoNgNARAzAdAjDATBSc4gJyIAxytjADhABYB2DDE8gVkShDOxoyhpG2xO0YLRLj0OIHNhQQApgDsU2MMDhg5cxSoC6kEITI0acGhDVA=
       */

      const {
        ClassicEditor,
        Autosave,
        Essentials,
        Paragraph,
        Autoformat,
        TextTransformation,
        LinkImage,
        Link,
        ImageBlock,
        ImageToolbar,
        ButtonView,
        BlockQuote,
        Bold,
        ImageInsertViaUrl,
        AutoImage,
        Table,
        TableToolbar,
        Heading,
        ImageTextAlternative,
        ImageCaption,
        ImageStyle,
        Indent,
        IndentBlock,
        ImageInline,
        Italic,
        List,
        MediaEmbed,
        MediaEmbedStyle,
        MediaEmbedToolbar,
        TodoList,
        Underline,
        Strikethrough,
        Subscript,
        Superscript,
        Alignment
      } = window.CKEDITOR;

      /**
       * This is a 24-hour evaluation key. Create a free account to use CDN: https://portal.ckeditor.com/checkout?plan=free
       */
      const LICENSE_KEY =
        'eyJhbGciOiJFUzI1NiJ9.eyJleHAiOjE3ODM5MDA3OTksImp0aSI6IjhhM2Y2ZGYzLWQzNzktNDUxMS1hMTZkLTVhMzFlNDc4YzRhYiIsImxpY2Vuc2VkSG9zdHMiOlsiKi53ZWJjb250YWluZXIuaW8iLCIqLmpzaGVsbC5uZXQiLCIqLmNzcC5hcHAiLCJjZHBuLmlvIiwiMTI3LjAuMC4xIiwibG9jYWxob3N0IiwiMTkyLjE2OC4qLioiLCIxMC4qLiouKiIsIjE3Mi4qLiouKiIsIioudGVzdCIsIioubG9jYWxob3N0IiwiKi5sb2NhbCJdLCJkaXN0cmlidXRpb25DaGFubmVsIjpbImNsb3VkIiwiZHJ1cGFsIiwic2giXSwibGljZW5zZVR5cGUiOiJldmFsdWF0aW9uIiwidmMiOiJhOTMzYTUzNiJ9.14i6YlGHpkpoOQcRxAYmKov2WGimMA5zv-JR6gJAAT8RUlLkXAvgTQ2DHDldeqmhLEseY09jpV593mhAFYBmVA';

      const editorConfig = {
        attachTo: document.querySelector('#editor'),
        root: {
          placeholder: 'Type or paste your content here!',
          initialData:
            '<h2>Evaluation license key 🔑</h2>\n<p>\n\tAn evaluation key is being used in this editor. <a href="https://portal.ckeditor.com/checkout?plan=free">\n\t\tCreate an account to use your own license keys.\n\t</a>\n</p>\n\n<h2>Congratulations on setting up CKEditor 5! 🎉</h2>\n<p>\n\tYou\'ve successfully created a CKEditor 5 project. This powerful text editor\n\twill enhance your application, enabling rich text editing capabilities that\n\tare customizable and easy to use.\n</p>\n<h3>What\'s next?</h3>\n<ol>\n\t<li>\n\t\t<strong>Integrate into your app</strong>: time to bring the editing into\n\t\tyour application. Take the code you created and add to your application.\n\t</li>\n\t<li>\n\t\t<strong>Explore features:</strong> Experiment with different plugins and\n\t\ttoolbar options to discover what works best for your needs.\n\t</li>\n\t<li>\n\t\t<strong>Customize your editor:</strong> Tailor the editor\'s\n\t\tconfiguration to match your application\'s style and requirements. Or\n\t\teven write your plugin!\n\t</li>\n</ol>\n<p>\n\tKeep experimenting, and don\'t hesitate to push the boundaries of what you\n\tcan achieve with CKEditor 5. Your feedback is invaluable to us as we strive\n\tto improve and evolve. Happy editing!\n</p>\n<h3>Helpful resources</h3>\n<ul>\n\t<li>📝 <a href="https://portal.ckeditor.com/checkout?plan=free">Trial sign up</a>,</li>\n\t<li>📕 <a href="https://ckeditor.com/docs/ckeditor5/latest/installation/index.html">Documentation</a>,</li>\n\t<li>⭐️ <a href="https://github.com/ckeditor/ckeditor5">GitHub</a> (star us if you can!),</li>\n\t<li>🏠 <a href="https://ckeditor.com">CKEditor Homepage</a>,</li>\n\t<li>🧑‍💻 <a href="https://ckeditor.com/ckeditor-5/demo/">CKEditor 5 Demos</a>,</li>\n</ul>\n<h3>Need help?</h3>\n<p>\n\tSee this text, but the editor is not starting up? Check the browser\'s\n\tconsole for clues and guidance. It may be related to an incorrect license\n\tkey if you use premium features or another feature-related requirement. If\n\tyou cannot make it work, file a GitHub issue, and we will help as soon as\n\tpossible!\n</p>\n'
        },
        toolbar: {
          items: [
            'undo',
            'redo',
            '|',
            'heading',
            '|',
            'bold',
            'italic',
            'underline',
            'strikethrough',
            'subscript',
            'superscript',
            '|',
            'link',
            'insertImageViaUrl',
            'mediaEmbed',
            'insertTable',
            'blockQuote',
            '|',
            'alignment',
            '|',
            'bulletedList',
            'numberedList',
            'todoList',
            'outdent',
            'indent',
            'mijnCustomBrowserKnop'
          ],
          shouldNotGroupWhenFull: false
        },
        plugins: [
          Alignment,
          Autoformat,
          AutoImage,
          Autosave,
          BlockQuote,
          Bold,
          Essentials,
          Heading,
          ImageBlock,
          ImageCaption,
          ImageInline,
          ImageInsertViaUrl,
          ImageStyle,
          ImageTextAlternative,
          ImageToolbar,
          Indent,
          IndentBlock,
          Italic,
          Link,
          LinkImage,
          List,
          MediaEmbed,
          MediaEmbedStyle,
          MediaEmbedToolbar,
          Paragraph,
          Strikethrough,
          Subscript,
          Superscript,
          Table,
          TableToolbar,
          TextTransformation,
          TodoList,
          Underline,
          function CustomFilePickerPlugin(editor) {
            editor.ui.componentFactory.add('mijnCustomBrowserKnop', locale => {
              const view = new ButtonView(locale);
              view.set({ label: 'Media bibliotheek', withText: true });
              view.on('execute', () => openMijnCustomFilePicker(editor));
              return view;
            });
          }
        ],
        licenseKey: LICENSE_KEY,
        autosave: {
          /* See: https://ckeditor.com/docs/ckeditor5/latest/features/autosave.html */
        },
        heading: {
          options: [
            {
              model: 'paragraph',
              title: 'Paragraph',
              class: 'ck-heading_paragraph'
            },
            {
              model: 'heading1',
              view: 'h1',
              title: 'Heading 1',
              class: 'ck-heading_heading1'
            },
            {
              model: 'heading2',
              view: 'h2',
              title: 'Heading 2',
              class: 'ck-heading_heading2'
            },
            {
              model: 'heading3',
              view: 'h3',
              title: 'Heading 3',
              class: 'ck-heading_heading3'
            },
            {
              model: 'heading4',
              view: 'h4',
              title: 'Heading 4',
              class: 'ck-heading_heading4'
            },
            {
              model: 'heading5',
              view: 'h5',
              title: 'Heading 5',
              class: 'ck-heading_heading5'
            },
            {
              model: 'heading6',
              view: 'h6',
              title: 'Heading 6',
              class: 'ck-heading_heading6'
            }
          ]
        },
        image: {
          toolbar: ['toggleImageCaption', 'imageTextAlternative', '|', 'imageStyle:inline', 'imageStyle:wrapText', 'imageStyle:breakText']
        },
        link: {
          addTargetToExternalLinks: true,
          defaultProtocol: 'https://',
          decorators: {
            toggleDownloadable: {
              mode: 'manual',
              label: 'Downloadable',
              attributes: {
                download: 'file'
              }
            }
          }
        },
        mediaEmbed: {
          toolbar: ['mediaEmbed:breakText', 'mediaEmbed:wrapText']
        },
        table: {
          contentToolbar: ['tableColumn', 'tableRow', 'mergeTableCells']
        }
      };

      ClassicEditor.create(document.querySelector('#body'), editorConfig);
    </script>
    <script>
        // Listen to localstorage change
        $(window).on('storage', message_receive);

        function message_receive(ev) {
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
                handle_image_change(parseInt(upload_id.dataset.count), data.webPath);
                if (count + 1 === parseInt(upload_id.dataset.count)) {
                    // add a new dummy if needed
                    count++;
                    add_dummy(count + 1);
                }
            } else {
                for (var i = 0; i < data.files.length; i++) {
                    // add all the images to the page
                    file = data.files[i];
                    count++;
                    document.getElementById('upload_id' + count).value = file.fileId;
                    document.getElementById('picture' + count).value = folder + file.fileName;
                    handle_image_change(count, data.webPath);
                    add_dummy(count + 1);
                }
            }
            document.getElementById('picturecount').value = count

        }

        function handle_image_change(count, path) {
            // preview the image
            $("#image-preview" + count).attr("src", function () {

                var value = $("#picture" + count).val();
                if (value.substr(0, 4) !== 'http') {
                    value = path + value;
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
                '<img alt="dummy placeholder" src="/img/dummy.png" class="img img-fluid" id="image-preview' + count + '">' +
                '<div class="input-group">' +
                '<input id="picture' + count + '" name="picture' + count + '" type="hidden">' +
                '<input id="upload_id' + count + '" name="upload_id' + count + '" data-count="' + count + '" type="hidden">' +
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
@endpush
