<?php $i=0; ?>
<tr>
    <td colspan="8" class="text-right">
    @if ($data->count() <= 1 )   
        {!! $data->count() . '  Record found' !!}   
    @else 
        {{ $data->count() . ' Records found' }}
    @endif   
    </td>
</tr>
@foreach($data as $row)
<tr>
    <td>  {{ ++$i }} </td>
    <td class="table-text-right tooltip-enable-mandatory" data-toggle="tooltip" data-container="#tableRoceMovement" data-original-title="{{ isset($row->comment) ? $row->comment:'No RAM, HDD info' }}" title="{{ isset($row->comment) ? $row->comment:'No RAM, HDD info' }}"data-placement="bottom" data-html="true" onmouseenter="tooltipEnterEvent($(this))" onmouseleave="tooltipLeaveEvent($(this))">{{ $row->equipmentname }} </td>
    <td>{{ $row->serialnumber }} </td>
    <td>{{ $row->computer_name }}  </td>
    <td>{{ $row->status }}</td>
    <td>
    {!! Form::open(['route' => ['equipment.destroy', $row->id], 'method' => 'delete']) !!}
        <div class='btn-group'>
            <a href="{!! route('equipment.show', [$row->id]) !!}" class='btn btn-default btn-xs'><i class="glyphicon glyphicon-eye-open"></i></a>
            <a href="{!! route('equipment.edit', [$row->id]) !!}" class='btn btn-default btn-xs'><i class="glyphicon glyphicon-edit"></i></a>
            {!! Form::button('<i class="glyphicon glyphicon-trash"></i>', ['type' => 'submit', 'class' => 'btn btn-danger btn-xs', 'onclick' => "return confirm('Are you sure?')"]) !!}
        </div>
        {!! Form::close() !!}
    </td>  
    </tr>
@endforeach
<tr>
    <td colspan="8" class="text-right">
        {!! $data->links() !!}
    </td>
</tr>