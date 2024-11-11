@extends('layouts.master')

@section('content')
    <div class="container mt-4">
        <h3 class="text-center text-dark font-weight-bold">MONTHLY SALES REPORT</h3>

        {{-- Filter Form --}}
        <form method="GET" action="{{ route('admin.msr') }}" class="row align-items-center mb-3">
            {{-- POS Selection --}}
            <div class="col-12 col-md-4 mb-2">
                <select class="form-control" name="search" id="search" required>
                    <option value="">Select POS by User ID...</option>
                    @foreach ($pos as $data)
                        <option value="{{ $data['id'] ?? '' }}"
                            {{ request('search') == ($data['id'] ?? '') ? 'selected' : '' }}>
                            {{ $data['name'] }} ({{ $data['pos_id'] ?? 'N/A' }})
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Month Filter --}}
            <div class="col-12 col-md-4 mb-2">
                <input type="month" name="month" id="month" class="form-control" value="{{ request('month') }}"
                    required>
            </div>

            {{-- Filter Button --}}
            <div class="col-12 col-md-auto mb-2">
                <button class="btn btn-info btn-block" type="submit">FILTER</button>
            </div>
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
                                <td>{{ optional($data->user)->name }}</td>
                                <td>{{ $data->total_billing_amount }}/-</td>
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
