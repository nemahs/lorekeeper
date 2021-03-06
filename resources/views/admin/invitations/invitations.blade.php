@extends('admin.layout')

@section('admin-title') Invitation Keys @endsection

@section('admin-content')
{!! breadcrumbs(['Admin Panel' => 'admin', 'Invitation Keys' => 'admin/invitations']) !!}

<h1>Invitation Keys</h1>

<p>Invitation keys can be used to register an account when the site is closed for registration (see the setting "is_registration_open" in <a href="{{ url('admin/settings') }}">Site Settings</a>). Users will be able to register by entering the code that is generated with the key. Generated invitations can be deleted only if they have not been used.</p>

{!! Form::open(['url' => 'admin/invitations/create', 'class' => 'text-right mb-3']) !!}
    {!! Form::submit('Generate New Invitation', ['class' => 'btn btn-primary']) !!}
{!! Form::close() !!}
@if(!count($invitations))
    <p>No invitations found.</p>
@else 
    {!! $invitations->render() !!}
    <table class="table table-sm setting-table">
        <thead>
            <tr>
                <th>Code</th>
                <th>Generated By</th>
                <th>Used By</th>
                <th>Created</th>
                <th>Used</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @foreach($invitations as $invitation)
                <tr>
                    <td>{{ $invitation->code }}</td>
                    <td>{!! $invitation->user->displayName !!}</td>
                    <td>@if($invitation->recipient_id) {!! $invitation->recipient->displayName !!} @else --- @endif</td>
                    <td>{{ format_date($invitation->created_at) }}</td>
                    <td>@if($invitation->created_at != $invitation->updated_at) {{ format_date($invitation->updated_at) }} @else --- @endif</td>
                    <td>
                        @if(!$invitation->recipient_id)
                            {!! Form::open(['url' => 'admin/invitations/delete/'.$invitation->id, 'class' => 'text-right']) !!}
                                {!! Form::submit('Delete', ['class' => 'btn btn-danger']) !!}
                            {!! Form::close() !!}
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>

    </table>
    {!! $invitations->render() !!}
@endif

@endsection