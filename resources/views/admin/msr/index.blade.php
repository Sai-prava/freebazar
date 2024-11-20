@extends('layouts.master')

@section('content')
    <div class="container mt-4">
        <h3 class="text-center text-dark font-weight-bold"><b>MONTHLY SALES REPORT</b></h3>

        <form method="GET" action="{{ route('admin.msr') }}" class="row align-items-center mb-3">
            <div class="col-12 col-md-4 mb-2">
                <select class="form-control" name="search" id="search">
                    <option value="">Select POS by POS ID...</option>
                    @foreach ($pos as $data)
                        <option value="{{ $data['id'] ?? '' }}"
                            {{ request('search') == ($data['id'] ?? '') ? 'selected' : '' }}>
                            {{ $data['name'] }} ({{ $data['pos_id'] ?? 'N/A' }})
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-12 col-md-4 mb-2">
                <input type="month" name="month" id="month" class="form-control" value="{{ request('month') }}">
            </div>

            <div class="col-12 col-md-auto mb-2">
                <button class="btn btn-info btn-block" type="submit">FILTER</button>
            </div>
        </form>

        <div class="col-md-12 mb-3 text-end">
            <form method="GET" action="{{ route('admin.msr.export') }}" class="d-inline">
                <input type="hidden" name="search" value="{{ request('search') }}">
                <input type="hidden" name="month" value="{{ request('month') }}">
                <button class="btn btn-danger" type="submit">EXPORT</button>
            </form>
        </div>

        <div class="table-responsive">
            <table class="table table-striped text-center">
                <thead>
                    <tr>
                        <th>Sl.No</th>
                        <th>MONTH</th>
                        <th>MOBILE NUMBER</th>
                        <th>TOTAL BILLING AMOUNT</th>
                        <th>SPONSOR EXPENDITURE</th>
                    </tr>
                </thead>
                <tbody>
                    @if ($monthlySales->isEmpty())
                        <tr>
                            <td colspan="5" class="text-center text-danger font-weight-bold">No Data Available</td>
                        </tr>
                    @else
                        @foreach ($monthlySales as $key => $data)
                            <tr>
                                <td>{{ $monthlySales->firstItem() + $key }}</td>
                                <td>{{ \Carbon\Carbon::createFromFormat('Y-m', $data->transaction_month)->format('F Y') }}
                                </td>
                                <td>{{ $data->mobilenumber }}</td>
                                <td>₹{{ $data->total_billing_amount ?? 0 }}/-</td>
                                <td>₹{{ $data->sponsor_expenditure }}/-</td>
                            </tr>
                        @endforeach
                    @endif
                </tbody>
            </table>
        </div>

        <div class="d-flex justify-content-center mt-3">
            {{ $monthlySales->links() }}
        </div>
    </div>

@endsection
