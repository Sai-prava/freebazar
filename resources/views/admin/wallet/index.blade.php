@extends('layouts.master')

@section('content')
    <div class="container" style="margin-top: 20px;">
        <h3 class="text-center"><b style="color: rgb(8, 7, 20)">WALLET DETAILS</b></h3>

        <div class="row">
            <!-- File Upload Form -->
            <div class="col-md-4 mb-3">
                <form action="{{ route('admin.wallet.upload') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="input-group">
                        <input type="file" class="form-control" name="file" accept=".xlsx">
                        <button class="btn btn-info" type="submit">UPLOAD WALLET</button>
                    </div>
                </form>
            </div>

            <!-- Search Form -->
            {{-- <div class="col-md-4 mb-3">
                <form method="GET" action="{{ route('admin.dsr') }}">
                    <div class="input-group">
                        <input type="number" class="form-control" name="search" placeholder="Search By...">
                        <button class="btn btn-info" type="submit">SEARCH</button>
                    </div>
                </form>
            </div> --}}

            <!-- Export Form -->
            <div class="col-md-6 mb-3 text-end">
                <form method="GET" action="{{ route('admin.wallet.export') }}">
                    <button class="btn btn-danger" type="submit">EXPORT</button>
                </form>
            </div>
        </div>

       
        <hr class="my-4">

        <!-- Data Table -->
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Sl.No</th>
                        <th>USER ID</th>
                        <th>USER NAME</th>
                        <th>MOBILE NUMBER</th> 
                        <th>PAYMENT MODE</th> 
                        <th>WALLET AMOUNT</th>
                                             
                    </tr>
                </thead>
                <tbody>
                  
                        @foreach ($walletBalance as $key => $data)
                            <tr>
                                <td>{{ $walletBalance->firstItem() + $key }}</td>
                                <td>{{ $data->user->user_id }}</td>
                                <td>{{ $data->user->name }}</td>
                                <td>{{ $data->mobilenumber }}</td>
                                <td>{{ $data->trans_type }}</td>
                                <td>₹{{ $data->wallet_amount }}/-</td>  
                            </tr>
                        @endforeach
                  
                </tbody>
            </table>
        </div>

        <!-- Pagination Links -->
        <div class="d-flex justify-content-center">
            {{ $walletBalance->links() }}
        </div>
    </div>
@endsection
