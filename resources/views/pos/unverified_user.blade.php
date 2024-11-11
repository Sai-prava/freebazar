@extends('pos.layouts.master')

@section('content')
<div class="container my-4">
    <h3 class="text-center text-dark mb-4"><b>Customer List</b></h3>

    <!-- Responsive table -->
    <div class="table-responsive">
        <table class="table table-striped table-bordered">
            <thead class="thead-dark">
                <tr>
                    <th scope="col">Sl.no</th>
                    <th scope="col">Name</th>
                    <th scope="col">UserId</th>
                    <th scope="col">Phone</th>
                    <th scope="col">Billing Amount</th>
                    <th scope="col">Payment Mode</th>
                    <th scope="col">Transation Date</th>
                    <th scope="col">Status</th>
                   
                </tr>
            </thead>
            <tbody>
                @foreach ($DsrList as $key => $customer)
                    <tr>
                        <td>{{ $DsrList->firstItem() + $key }}</td>
                        <td>{{ $customer->user->name ?? 'N/A' }}</td>
                        <td>{{ $customer->user->user_id ?? 'N/A' }}</td>
                        <td>{{ $customer->user->mobilenumber ?? 'N/A' }}</td>
                        <td>{{ $customer->billing_amount }}/-</td>
                        <td>{{ $customer->pay_by }}/-</td>
                        <td>{{ date('d-m-Y', strtotime($customer->transaction_date)) }}</td>
                        <td>
                            @if ($customer->status == 0)
                                <a href="{{ route('pos.wallet.updateStatus', $customer->id) }}" class="btn btn-danger btn-sm">Unverified</a>
                            @else
                                <a href="{{ route('pos.wallet.updateStatus', $customer->id) }}" class="btn btn-success btn-sm">Verified</a>
                            @endif
                        </td>
                       
                    </tr>
                @endforeach
            </tbody>
        </table>
        <div class="d-flex justify-content-center">
            {{ $DsrList->links() }}
        </div>
    </div>
</div>
@endsection
