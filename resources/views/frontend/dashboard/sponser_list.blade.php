@extends('frontend.dashboard.layouts.master')

@section('content')
    <div class="card">
        <div class="card-header">
            <div class="float-start">
                <h4><b>Sponcer List</b></h4>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>
                                Sl.No
                            </th>
                            <th>
                                Name
                            </th>
                            <th>
                                Created ON
                            </th>
                            <th>
                                Activated ON
                            </th>
                            <th>
                                Sup Activated ON
                            </th>
                            <th>
                                Action
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($sponcer as $key => $data)
                            <tr>
                                <td>{{ $key + 1 }}</td>
                                <td>
                                    @if ($data->user)
                                        {{ $data->user->name }}
                                    @endif
                                </td>
                                <td>{{ $data->created_at->format('Y-m-d') }}</td>
                                <td></td>
                                <td></td>
                                <td>
                                    <a href="{{ route('frontend.index') }}">
                                        <i class="bi bi-bookmark"
                                            style="background-color: rgb(95, 54, 161); padding: 8.5px; border-radius: 12px; color: rgb(247, 241, 241);"></i>
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        {{-- <div class="card-footer clearfix">
        {{ $users->links() }}
    </div> --}}
    </div>
@endsection
