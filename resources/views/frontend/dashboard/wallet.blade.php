@extends('frontend.dashboard.layouts.master')

@section('content')
    <div class="container" style="margin-top: 20px;">

        <div class="col-md-6">

            <h5>CURRENT WALLET BALANCE: <br> <span style="font-size:55px; color:rgb(0, 162, 255); "><i class="fa fa-inr"
                        aria-hidden="true"></i> {{ $walletBalance }}/-</span></h5>
            <br>
        </div>
        <hr class="my-4">

        <!-- Data Table -->
      <div class="row">
        <div>
            <h4>MY WALLET</h4>
        </div>
        <div class="container">
            <div class="col-md-6">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Sl.No</th>
                                <th>USER ID</th>
                                <th>INVOICE</th>
                                <th>CREDIT</th>
                                <th>DEBIT</th>
                                <th>MOBILE NUMBER</th>
                                <th>DATE OF TRANSACTION</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($userWallet as $key => $data)
                                <tr>
                                    <td>{{  $key + 1}}</td>
                                    <td>{{ $data->user_id }}</td>
                                    <td>{{ $data->invoice }}</td>
                                    <td>₹{{ $data->wallet_amount ?? 0 }}/-</td>
                                    <td>₹{{ $data->used_amount ?? 0}}/-</td>
                                    <td>{{ $data->mobilenumber }}</td>        
                                    <td>{{ $data->created_at }}</td>        
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
      </div>

        <!-- Pagination Links -->
        <div class="d-flex justify-content-center">
            {{-- {{ $walletBalance->links() }} --}}
        </div>
    </div>
@endsection
