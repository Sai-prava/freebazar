@extends('layouts.master')

@section('content')
    <div class="container mt-4">
        <h3 class="text-center text-dark font-weight-bold">MONTHLY SALES REPORT</h3>

        {{-- Search Form --}}
        <form method="GET" action="{{ route('admin.msr') }}" class="row align-items-center mb-3">
            <div class="col-12 col-md-4 mb-2">
                <input type="number" class="form-control" name="search" placeholder="Search By..." id="search" value="{{ request('search') }}">
            </div>
            <div class="col-12 col-md-auto mb-2">
                <button class="btn btn-info btn-block" type="submit">SEARCH</button>
            </div>
        </form>

        {{-- Month Filter --}}
        <form method="GET" action="{{ route('admin.msr') }}" class="row align-items-center mb-3">
            <div class="col-12 col-md-4 mb-2">
                <input type="month" name="month" id="month" class="form-control" value="{{ request('month') }}">
            </div>
            <div class="col-12 col-md-auto mb-2">
                <button class="btn btn-info btn-block" type="submit">FILTER</button>
            </div>
        </form>

        {{-- Export Button --}}
        <form method="GET" action="{{ route('admin.msr.export') }}" class="text-md-right mb-3">
            <input type="hidden" name="search" value="{{ request('search') }}">
            <button class="btn btn-danger" type="submit">EXPORT</button>
        </form>

        {{-- Sales Report Table --}}
        <div class="table-responsive">
            <table class="table table-striped text-center">
                <thead>
                    <tr>
                        <th>Sl.No</th>
                        <th>MONTH</th>
                        <th>MOBILE NUMBER</th>
                        <th>NAME</th>
                        <th>TOTAL BILLING AMOUNT</th>
                        <th>SPONSOR EXPENDITURE</th>
                    </tr>
                </thead>
                <tbody>
                    @if ($monthlySales->isEmpty())
                        <tr>
                            <td colspan="6" class="text-center text-danger font-weight-bold">No Data Available</td>
                        </tr>
                    @else
                        @foreach ($monthlySales as $key => $data)
                            <tr>
                                <td>{{ $monthlySales->firstItem() + $key }}</td>
                                <td>{{ \Carbon\Carbon::createFromFormat('Y-m', $data->transaction_month)->format('F Y') }}</td>
                                <td>{{ $data->mobilenumber }}</td>
                                <td>{{ optional($data->user)->name }}</td>
                                <td>{{ $data->total_billing_amount }}/-</td>
                                <td></td>
                            </tr>
                        @endforeach
                    @endif
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        <div class="d-flex justify-content-center mt-3">
            {{ $monthlySales->links() }}
        </div>
    </div>
@endsection
