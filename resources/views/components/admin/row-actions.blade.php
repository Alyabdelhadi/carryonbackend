@props(['module', 'edit' => null, 'delete' => null, 'view' => null, 'deleteText' => null])
{{--
    Row buttons, each behind its permission tick. `delete` is the legacy
    GET delete URL (confirmed with a dialog). Extra buttons go in the slot.
--}}
<div class="d-inline-flex align-items-center justify-content-end flex-nowrap">
    {{ $slot }}
    @if($view)
        @can($module . '.view')
            <a class="btn btn-icon btn-light" href="{{ $view }}" data-toggle="tooltip" title="View"><i class="feather icon-eye"></i></a>
        @endcan
    @endif
    @if($edit)
        @can($module . '.edit')
            <a class="btn btn-icon btn-info" href="{{ $edit }}" data-toggle="tooltip" title="Edit"><i class="feather icon-edit-2"></i></a>
        @endcan
    @endif
    @if($delete)
        @can($module . '.delete')
            <button type="button" class="btn btn-icon btn-danger" data-toggle="tooltip" title="Delete" onclick="confirmAlert('{{ $delete }}'{{ $deleteText ? ', ' . e(json_encode($deleteText)) : '' }})"><i class="feather icon-trash-2"></i></button>
        @endcan
    @endif
</div>
