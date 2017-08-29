@extends('voyager::master')

@section('page_title', __('voyager.generic.viewing').' '.$dataType->display_name_plural)

@section('page_header')
    <h1 class="page-title">
        <i class="{{ $dataType->icon }}"></i> {{ $dataType->display_name_plural }}
        @if (Voyager::can('add_'.$dataType->name))
            <a href="{{ route('voyager.'.$dataType->slug.'.create') }}" class="btn btn-success">
                <i class="voyager-plus"></i> {{ __('voyager.generic.add_new') }}
            </a>
        @endif
    </h1>
    @include('voyager::multilingual.language-selector')
@stop

@section('content')
    <div class="page-content browse container-fluid">
        @include('voyager::alerts')
        <div class="row">

            <div class="col-md-6 messages-master">
                <div class="panel panel-bordered">
                    <div class="panel-body table-responsive">
                        <table id="dataTable" class="row table table-hover">
                            <thead>
                                <tr>
                                    @foreach($dataType->browseRows as $row)
                                    <th>{{ $row->display_name }}</th>
                                    @endforeach
                                    <th class="actions">{{ __('voyager.generic.actions') }}</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                            <tfoot>
                                @foreach($dataType->browseRows as $row)
                                    <th></th>
                                @endforeach
                            </tfoot>
                        </table>
                        @if (isset($dataType->server_side) && $dataType->server_side)
                            <div class="pull-left">
                                <div role="status" class="show-res" aria-live="polite">{{ trans_choice(
                                    'voyager.generic.showing_entries', $dataTypeContent->total(), [
                                        'from' => $dataTypeContent->firstItem(),
                                        'to' => $dataTypeContent->lastItem(),
                                        'all' => $dataTypeContent->total()
                                    ]) }}</div>
                            </div>
                            <div class="pull-right">
                                {{ $dataTypeContent->links() }}
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="col-md-6 messages-detail">
                <div class="panel panel-bordered">
                    <div class="panel-body">
                        <div class="messages-detail-list">
                        </div>

                        <div class="messages-detail-compose">
                            <div class="input-group">
                                <textarea class="form-control custom-control" rows="3"></textarea>
                                <span class="input-group-addon btn btn-primary">Enviar</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <div class="modal modal-danger fade" tabindex="-1" id="delete_modal" role="dialog">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="{{ __('voyager.generic.close') }}"><span
                                aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title"><i class="voyager-trash"></i> {{ __('voyager.generic.delete_question') }} {{ strtolower($dataType->display_name_singular) }}?</h4>
                </div>
                <div class="modal-footer">
                    <form action="{{ route('voyager.'.$dataType->slug.'.index') }}" id="delete_form" method="POST">
                        {{ method_field("DELETE") }}
                        {{ csrf_field() }}
                        <input type="submit" class="btn btn-danger pull-right delete-confirm"
                                 value="{{ __('voyager.generic.delete_confirm') }} {{ strtolower($dataType->display_name_singular) }}">
                    </form>
                    <button type="button" class="btn btn-default pull-right" data-dismiss="modal">{{ __('voyager.generic.cancel') }}</button>
                </div>
            </div><!-- /.modal-content -->
        </div><!-- /.modal-dialog -->
    </div><!-- /.modal -->
@stop

@section('css')
@if(!$dataType->server_side && config('dashboard.data_tables.responsive'))
<link rel="stylesheet" href="{{ voyager_asset('lib/css/responsive.dataTables.min.css') }}">
@endif
@stop

@section('javascript')
    <!-- DataTables -->
    @if(!$dataType->server_side && config('dashboard.data_tables.responsive'))
        <script src="{{ voyager_asset('lib/js/dataTables.responsive.min.js') }}"></script>
    @endif
    @if($isModelTranslatable)
        <script src="{{ voyager_asset('js/multilingual.js') }}"></script>
    @endif
    <script>
        $(document).ready(function () {
            @if ($isModelTranslatable)
                $('.side-body').multilingual();
            @endif

            $('#dataTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: '{!! route('messages.api') !!}',
                    data: function (d) {
                        d.columns.forEach(function (column) {
                            if (column.name && column.name.indexOf('.') != -1) {
                                const name = column.name.replace('.', '_');
                                const searchTerm = $('input[name=' + name + ']').val();

                                if (searchTerm && searchTerm.trim().length > 0) d[name] = searchTerm.trim();
                            }
                        });
                    }
                },
                columns: [
                    { data: 'message', name: 'message' },
                    { data: 'direction', name: 'direction', visible: false },
                    { data: 'rating.rating', name: 'rating.rating', visible: false },
                    { data: 'created_at', name: 'created_at' }
                ],
                order: [[3, 'desc']],
                mark: true,
                initComplete: function () {
                    this.api().columns().every(function () {
                        const column = this;
                        const input = document.createElement('input');

                        if (column.name) input.name = column.name.replace('.', '_');

                        $(input).appendTo($(column.footer()).empty())
                        .on('change', function () {
                            const val = $.fn.dataTable.util.escapeRegex($(this).val().trim());

                            column.search($(this).val()).draw();
                        })
                        .closest('tr').addClass('row-search');
                    });

                    $('#dataTable tr:nth-child(2)').click();
                }
            });
        }).on('click', 'tr', function() {
            selectRow(this);
        });

        var deleteFormAction;

        $('td').on('click', '.delete', function (e) {
            var form = $('#delete_form')[0];

            if (!deleteFormAction) { // Save form action initial value
                deleteFormAction = form.action;
            }

            form.action = deleteFormAction.match(/\/[0-9]+$/)
                ? deleteFormAction.replace(/([0-9]+$)/, $(this).data('id'))
                : deleteFormAction + '/' + $(this).data('id');
            console.log(form.action);

            $('#delete_modal').modal('show');
        });

        const messagePanel = function(direction) {
            const type = direction === 'in' ? 'primary' : 'default';

            return $('<div>', { class: 'panel panel-' + type + ' message message-' + direction });
        }

        const messageHeading = function(content) {
            return $('<div>', {
                class: 'panel-heading message-heading',
                text: content
             });
        }

        const messageBody = function(content) {
            return $('<div>', {
                class: 'panel-body message-body',
                text: content
             });
        }

        const buildMessage = function(content) {
            const message = messagePanel(content.direction);
            const heading = messageHeading(content.created_at);
            const body = messageBody(content.message);

            message.append(heading);
            message.append(body);

            return message;
        }

        const buildThread = function(messages) {
            const thread = $('.messages-detail-list').first().empty();

            for (const message of messages) {
               thread.append(buildMessage(message));
            }
        }

        const selectRow = function(row) {
            const rowData = $('#dataTable').DataTable().row(row).data();

            $('#dataTable .row-selected').removeClass('row-selected');

            if (!$(row).hasClass('row-search')) $(this).addClass('row-selected');

            if (rowData) {
                const ratingID = rowData.rating_id;

                fetch('/admin/ratings/' + ratingID + '/messages', {
                    method: 'GET',
                    credentials: 'include'
                })
                .then(function(response) {
                    return response.json();
                })
                .then(function(response) {
                    buildThread(response);
                })
            }
        }
    </script>
@stop